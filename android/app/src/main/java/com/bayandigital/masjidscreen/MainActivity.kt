package com.bayandigital.masjidscreen

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import com.bayandigital.masjidscreen.data.PrayerResponse
import com.bayandigital.masjidscreen.network.PrayerApi
import com.bayandigital.masjidscreen.network.PrayerRepository
import com.bayandigital.masjidscreen.setup.MasjidSetupStore
import com.bayandigital.masjidscreen.setup.SetupScreen
import com.bayandigital.masjidscreen.ui.ScreenState
import com.bayandigital.masjidscreen.ui.SmartScreen
import com.jakewharton.retrofit2.converter.kotlinx.serialization.asConverterFactory
import java.io.IOException
import java.time.LocalTime
import java.time.format.DateTimeFormatter
import kotlinx.coroutines.delay
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
            var requestedId by remember { mutableStateOf(store.masjidId.orEmpty()) }
            var requestVersion by remember { mutableIntStateOf(if (store.isConfigured) 1 else 0) }
            var payload by remember { mutableStateOf<PrayerResponse?>(null) }
            var isConnecting by remember { mutableStateOf(false) }
            var connectionError by remember { mutableStateOf<String?>(null) }
            var currentTime by remember { mutableStateOf(currentClockTime()) }

            LaunchedEffect(requestVersion) {
                if (requestVersion == 0 || requestedId.isBlank()) return@LaunchedEffect

                isConnecting = true
                connectionError = null
                payload = null

                runCatching { repository.sync(requestedId) }
                    .onSuccess { response ->
                        store.masjidId = requestedId
                        requestedId = response.masjid.id
                        payload = response
                    }
                    .onFailure { error -> connectionError = friendlyConnectionError(error) }

                isConnecting = false
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
            } ?: run {
                SetupScreen(
                    initialId = requestedId,
                    isConnecting = isConnecting,
                    errorMessage = connectionError,
                    onSave = { id ->
                        requestedId = id
                        requestVersion += 1
                    }
                )
            }
        }
    }

    private fun friendlyConnectionError(error: Throwable): String = when {
        error is HttpException && error.code() == 403 ->
            "This ID is not approved yet. Ask an administrator to approve the registration."
        error is HttpException && error.code() == 404 ->
            "Unique ID not found. Check the ID and try again."
        error is HttpException ->
            "The server could not connect this display (error ${error.code()}). Please try again."
        error is IOException ->
            "Cannot reach bayanDigital. Check the TV internet connection and try again."
        else -> "Unable to load this display. Please check the ID and try again."
    }

    private fun currentClockTime(): String = LocalTime.now().format(CLOCK_FORMAT)

    companion object {
        private const val API_BASE_URL = "https://bayandigital.rarecreation.xyz/"
        private val CLOCK_FORMAT: DateTimeFormatter = DateTimeFormatter.ofPattern("HH:mm:ss")
    }
}
