package com.bayandigital.masjidscreen

import android.os.Build
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import com.bayandigital.masjidscreen.data.MasjidSearchResult
import com.bayandigital.masjidscreen.data.PairingRequestBody
import com.bayandigital.masjidscreen.data.PairingRequestResponse
import com.bayandigital.masjidscreen.data.PairingStatusResponse
import com.bayandigital.masjidscreen.data.PrayerResponse
import com.bayandigital.masjidscreen.network.PrayerApi
import com.bayandigital.masjidscreen.network.PrayerRepository
import com.bayandigital.masjidscreen.setup.MasjidSetupStore
import com.bayandigital.masjidscreen.setup.PairingScreen
import com.bayandigital.masjidscreen.setup.SetupScreen
import com.bayandigital.masjidscreen.ui.ScreenState
import com.bayandigital.masjidscreen.ui.SmartScreen
import com.jakewharton.retrofit2.converter.kotlinx.serialization.asConverterFactory
import java.io.IOException
import java.time.LocalTime
import java.time.format.DateTimeFormatter
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import retrofit2.HttpException
import retrofit2.Retrofit

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val preferences = getSharedPreferences("screen_setup", MODE_PRIVATE)
        val store = MasjidSetupStore(preferences)
        val json = Json { ignoreUnknownKeys = true }
        val api = Retrofit.Builder()
            .baseUrl(API_BASE_URL)
            .client(OkHttpClient.Builder().build())
            .addConverterFactory(json.asConverterFactory("application/json".toMediaType()))
            .build()
            .create(PrayerApi::class.java)
        val repository = PrayerRepository(api, preferences, json)

        setContent {
            val scope = rememberCoroutineScope()
            var connectVersion by remember { mutableIntStateOf(if (store.isConfigured) 1 else 0) }
            var payload by remember { mutableStateOf<PrayerResponse?>(null) }
            var results by remember { mutableStateOf(emptyList<MasjidSearchResult>()) }
            var pairing by remember { mutableStateOf<PairingRequestResponse?>(null) }
            var isSearching by remember { mutableStateOf(false) }
            var isChecking by remember { mutableStateOf(false) }
            var setupError by remember { mutableStateOf<String?>(null) }
            var pairingMessage by remember { mutableStateOf<String?>(null) }
            var currentTime by remember { mutableStateOf(currentClockTime()) }

            LaunchedEffect(connectVersion) {
                if (connectVersion == 0 || !store.isConfigured) return@LaunchedEffect

                runCatching { repository.sync(store.masjidId!!, store.deviceToken!!) }
                    .onSuccess { payload = it }
                    .onFailure { error ->
                        if (error is HttpException && error.code() in listOf(401, 403)) {
                            store.clearPairing()
                            setupError = "This TV pairing is no longer valid. Search and request approval again."
                        } else {
                            setupError = friendlyConnectionError(error)
                        }
                    }
            }

            LaunchedEffect(payload) {
                if (payload == null) return@LaunchedEffect
                while (true) {
                    currentTime = currentClockTime()
                    delay(1_000)
                }
            }

            payload?.let { screenPayload ->
                BackHandler { payload = null }
                SmartScreen(payload = screenPayload, currentTime = currentTime, state = ScreenState.Idle)
            } ?: pairing?.let { pairingRequest ->
                PairingScreen(
                    appVersion = BuildConfig.VERSION_NAME,
                    pairing = pairingRequest,
                    isChecking = isChecking,
                    message = pairingMessage,
                    onCheck = {
                        scope.launch {
                            isChecking = true
                            pairingMessage = null
                            runCatching { api.pairingStatus(pairingRequest.requestId, pairingRequest.pairingCode) }
                                .onSuccess { status ->
                                    if (status.status == "approved") {
                                        completePairing(status, store)
                                        pairing = null
                                        connectVersion += 1
                                    } else {
                                        pairingMessage = status.message ?: "Waiting for administrator approval."
                                    }
                                }
                                .onFailure { pairingMessage = friendlyConnectionError(it) }
                            isChecking = false
                        }
                    },
                    onCancel = {
                        pairing = null
                        pairingMessage = null
                    }
                )
            } ?: SetupScreen(
                appVersion = BuildConfig.VERSION_NAME,
                isSearching = isSearching,
                errorMessage = setupError,
                results = results,
                onSearch = { query ->
                    scope.launch {
                        isSearching = true
                        setupError = null
                        runCatching { api.searchMasjids(query) }
                            .onSuccess { response ->
                                results = response.results
                                if (results.isEmpty()) setupError = "No approved masjid or surau matched your search."
                            }
                            .onFailure { setupError = friendlyConnectionError(it) }
                        isSearching = false
                    }
                },
                onSelect = { masjid ->
                    scope.launch {
                        isSearching = true
                        setupError = null
                        runCatching {
                            api.requestPairing(
                                masjid.id,
                                PairingRequestBody("${Build.MANUFACTURER} ${Build.MODEL}".trim())
                            )
                        }.onSuccess { request ->
                            pairing = request
                            pairingMessage = "Waiting for an administrator to approve this TV."
                        }.onFailure { setupError = friendlyConnectionError(it) }
                        isSearching = false
                    }
                }
            )
        }
    }

    private fun completePairing(status: PairingStatusResponse, store: MasjidSetupStore) {
        val masjidId = requireNotNull(status.masjidId)
        val token = requireNotNull(status.deviceToken)
        store.masjidId = masjidId
        store.deviceToken = token
    }

    private fun friendlyConnectionError(error: Throwable): String = when {
        error is HttpException && error.code() == 401 -> "This TV is not paired or its access was revoked."
        error is HttpException && error.code() == 403 -> "This request is not authorized."
        error is HttpException && error.code() == 404 -> "The requested masjid, surau, or pairing was not found."
        error is HttpException && error.code() == 422 -> "Enter at least two characters and check the search text."
        error is HttpException -> "The server returned error ${error.code()}. Please try again."
        error is IOException -> "Cannot reach bayanDigital. Check the TV internet connection and try again."
        else -> "Unable to complete this request. Please try again."
    }

    private fun currentClockTime(): String = LocalTime.now().format(CLOCK_FORMAT)

    companion object {
        private const val API_BASE_URL = "https://bayandigital.rarecreation.xyz/"
        private val CLOCK_FORMAT: DateTimeFormatter = DateTimeFormatter.ofPattern("HH:mm:ss")
    }
}
