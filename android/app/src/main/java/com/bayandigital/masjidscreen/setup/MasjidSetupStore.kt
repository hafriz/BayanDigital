package com.bayandigital.masjidscreen.setup

import android.content.SharedPreferences

class MasjidSetupStore(private val prefs: SharedPreferences) {
    var masjidId: String?
        get() = prefs.getString(KEY_MASJID_ID, null)
        set(value) = prefs.edit().putString(KEY_MASJID_ID, value?.trim()?.uppercase()).apply()

    var deviceToken: String?
        get() = prefs.getString(KEY_DEVICE_TOKEN, null)
        set(value) = prefs.edit().putString(KEY_DEVICE_TOKEN, value).apply()

    val isConfigured: Boolean get() = !masjidId.isNullOrBlank() && !deviceToken.isNullOrBlank()

    fun clearPairing() {
        prefs.edit().remove(KEY_MASJID_ID).remove(KEY_DEVICE_TOKEN).apply()
    }

    companion object {
        private const val KEY_MASJID_ID = "masjid_unique_id"
        private const val KEY_DEVICE_TOKEN = "screen_device_token"
    }
}
