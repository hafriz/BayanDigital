package com.bayandigital.masjidscreen.network

import android.content.SharedPreferences
import com.bayandigital.masjidscreen.data.PrayerResponse
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json
import retrofit2.HttpException

class PrayerRepository(
    private val api: PrayerApi,
    private val prefs: SharedPreferences,
    private val json: Json = Json { ignoreUnknownKeys = true }
) {
    suspend fun sync(masjidId: String, deviceToken: String): PrayerResponse {
        val normalizedId = masjidId.trim().uppercase()

        return runCatching {
            api.screenPayload(normalizedId, "Bearer $deviceToken").also { response ->
                prefs.edit().putString(cacheKey(normalizedId), json.encodeToString(response)).apply()
            }
        }.getOrElse { error ->
            // Never bypass rejected, suspended, pending, or unknown IDs with cached data.
            if (error is HttpException && error.code() in 400..499) throw error

            val cached = prefs.getString(cacheKey(normalizedId), null) ?: throw error
            json.decodeFromString(cached)
        }
    }

    companion object {
        private const val CACHE_KEY_PREFIX = "last_prayer_payload_"

        private fun cacheKey(masjidId: String) = CACHE_KEY_PREFIX + masjidId
    }
}
