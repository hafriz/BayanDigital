package com.bayandigital.masjidscreen.setup

import android.content.SharedPreferences

class MasjidSetupStore(private val prefs: SharedPreferences) {
    var masjidId: String?
        get() = prefs.getString(KEY_MASJID_ID, null)
        set(value) = prefs.edit().putString(KEY_MASJID_ID, value?.trim()?.uppercase()).apply()

    val isConfigured: Boolean get() = !masjidId.isNullOrBlank()

    companion object {
        private const val KEY_MASJID_ID = "masjid_unique_id"
    }
}
