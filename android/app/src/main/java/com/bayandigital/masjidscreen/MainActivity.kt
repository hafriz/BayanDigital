package com.bayandigital.masjidscreen

import android.os.Build
import android.os.Bundle
import android.content.Intent
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.WindowInsetsControllerCompat
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
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
import com.bayandigital.masjidscreen.ui.ScreenConnectionStatus
import com.bayandigital.masjidscreen.ui.SmartScreen
import com.jakewharton.retrofit2.converter.kotlinx.serialization.asConverterFactory
import java.io.IOException
import java.time.LocalTime
import java.time.format.DateTimeFormatter
import kotlinx.coroutines.CancellationException
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
        wakeDisplay()

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
            var connectionStatus by remember { mutableStateOf(ScreenConnectionStatus.Syncing) }
            var lastSuccessfulSyncMillis by remember { mutableStateOf<Long?>(null) }
            val scheduledSleep = payload?.let { DisplayPowerSchedule.isSleeping(it.masjid, it.timeline, currentTime) } ?: false

            LaunchedEffect(payload?.masjid, payload?.timeline) {
                payload?.let { DisplayPowerSchedule.scheduleWake(this@MainActivity, it.masjid, it.timeline) }
            }

            LaunchedEffect(scheduledSleep) {
                applyDisplayPowerState(scheduledSleep)
            }

            LaunchedEffect(connectVersion) {
                if (connectVersion == 0 || !store.isConfigured) return@LaunchedEffect

                while (store.isConfigured) {
                    if (payload == null) connectionStatus = ScreenConnectionStatus.Syncing
                    try {
                        val result = repository.sync(store.masjidId!!, store.deviceToken!!)
                        payload = result.payload
                        lastSuccessfulSyncMillis = result.lastSuccessfulSyncMillis
                        connectionStatus = if (result.isConnected) ScreenConnectionStatus.Connected else ScreenConnectionStatus.Offline
                        setupError = null
                        delay(if (result.isConnected) CONNECTED_REFRESH_MILLIS else OFFLINE_RETRY_MILLIS)
                    } catch (error: CancellationException) {
                        throw error
                    } catch (error: Throwable) {
                        if (error is HttpException && error.code() in listOf(401, 403)) {
                            store.clearPairing()
                            payload = null
                            setupError = "This TV pairing is no longer valid. Search and request approval again."
                            break
                        } else {
                            connectionStatus = ScreenConnectionStatus.Offline
                            setupError = friendlyConnectionError(error)
                            delay(OFFLINE_RETRY_MILLIS)
                        }
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

            if (scheduledSleep) {
                BackHandler { }
                Box(Modifier.fillMaxSize().background(Color.Black))
            } else payload?.let { screenPayload ->
                BackHandler { payload = null }
                SmartScreen(
                    payload = screenPayload,
                    currentTime = currentTime,
                    state = ScreenState.Idle,
                    connectionStatus = connectionStatus,
                    lastSuccessfulSyncMillis = lastSuccessfulSyncMillis
                )
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

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        if (intent.getBooleanExtra(DisplayWakeReceiver.EXTRA_SCHEDULED_WAKE, false)) wakeDisplay()
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (hasFocus) enableImmersiveMode()
    }

    private fun enableImmersiveMode() {
        WindowCompat.setDecorFitsSystemWindows(window, false)
        WindowInsetsControllerCompat(window, window.decorView).apply {
            hide(WindowInsetsCompat.Type.systemBars())
            systemBarsBehavior = WindowInsetsControllerCompat.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
        }
    }

    private fun wakeDisplay() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
            setTurnScreenOn(true)
            setShowWhenLocked(true)
        } else {
            @Suppress("DEPRECATION")
            window.addFlags(WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON or WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED)
        }
        applyDisplayPowerState(false)
    }

    private fun applyDisplayPowerState(sleeping: Boolean) {
        if (sleeping) {
            window.clearFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        } else {
            window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
            enableImmersiveMode()
        }
        window.attributes = window.attributes.apply {
            screenBrightness = if (sleeping) WindowManager.LayoutParams.BRIGHTNESS_OVERRIDE_OFF
            else WindowManager.LayoutParams.BRIGHTNESS_OVERRIDE_NONE
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
        private const val CONNECTED_REFRESH_MILLIS = 60_000L
        private const val OFFLINE_RETRY_MILLIS = 30_000L
        private val CLOCK_FORMAT: DateTimeFormatter = DateTimeFormatter.ofPattern("HH:mm:ss")
    }
}
