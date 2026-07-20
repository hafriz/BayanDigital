package com.bayandigital.masjidscreen.network

import android.content.SharedPreferences
import com.bayandigital.masjidscreen.data.PrayerResponse
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json

class PrayerRepository(
    private val api: PrayerApi,
    private val prefs: SharedPreferences,
    private val json: Json = Json { ignoreUnknownKeys = true }
) {
    suspend fun sync(masjidId: String): PrayerResponse = runCatching {
        api.screenPayload(masjidId).also { response ->
            prefs.edit().putString(CACHE_KEY, json.encodeToString(response)).apply()
        }
    }.getOrElse {
        val cached = prefs.getString(CACHE_KEY, null) ?: throw it
        json.decodeFromString(cached)
    }

    companion object {
        private const val CACHE_KEY = "last_prayer_payload"
    }
}
