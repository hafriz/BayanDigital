package com.bayandigital.masjidscreen.network

import android.content.SharedPreferences
import com.bayandigital.masjidscreen.data.PrayerResponse
import kotlinx.coroutines.CancellationException
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json
import retrofit2.HttpException

data class PrayerSyncResult(
    val payload: PrayerResponse,
    val isConnected: Boolean,
    val lastSuccessfulSyncMillis: Long?
)

class PrayerRepository(
    private val api: PrayerApi,
    private val prefs: SharedPreferences,
    private val json: Json = Json { ignoreUnknownKeys = true }
) {
    suspend fun sync(masjidId: String, deviceToken: String): PrayerSyncResult {
        val normalizedId = masjidId.trim().uppercase()

        return runCatching {
            api.screenPayload(normalizedId, "Bearer $deviceToken").let { response ->
                val syncedAt = System.currentTimeMillis()
                prefs.edit()
                    .putString(cacheKey(normalizedId), json.encodeToString(response))
                    .putLong(lastSyncKey(normalizedId), syncedAt)
                    .apply()
                PrayerSyncResult(response, true, syncedAt)
            }
        }.getOrElse { error ->
            if (error is CancellationException) throw error

            // Never bypass rejected, suspended, pending, or unknown IDs with cached data.
            if (error is HttpException && error.code() in 400..499) throw error

            val cached = prefs.getString(cacheKey(normalizedId), null) ?: throw error
            PrayerSyncResult(
                payload = json.decodeFromString(cached),
                isConnected = false,
                lastSuccessfulSyncMillis = prefs.getLong(lastSyncKey(normalizedId), 0L).takeIf { it > 0L }
            )
        }
    }

    companion object {
        private const val CACHE_KEY_PREFIX = "last_prayer_payload_"
        private const val LAST_SYNC_KEY_PREFIX = "last_prayer_sync_"

        private fun cacheKey(masjidId: String) = CACHE_KEY_PREFIX + masjidId
        private fun lastSyncKey(masjidId: String) = LAST_SYNC_KEY_PREFIX + masjidId
    }
}
