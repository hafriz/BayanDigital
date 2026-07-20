package com.bayandigital.masjidscreen

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import com.bayandigital.masjidscreen.setup.MasjidSetupStore
import com.bayandigital.masjidscreen.setup.SetupScreen

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val store = MasjidSetupStore(getSharedPreferences("screen_setup", MODE_PRIVATE))
        setContent {
            SetupScreen { id -> store.masjidId = id }
        }
    }
}
