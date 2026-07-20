package com.bayandigital.masjidscreen.ui

import androidx.compose.animation.Crossfade
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.bayandigital.masjidscreen.data.PrayerResponse

sealed interface ScreenState {
    data object Idle : ScreenState
    data class AzanAlert(val prayerName: String) : ScreenState
    data class IqamahCountdown(val prayerName: String, val remainingSeconds: Int) : ScreenState
    data class SilentMode(val message: String) : ScreenState
}

@Composable
fun SmartScreen(payload: PrayerResponse, currentTime: String, state: ScreenState) {
    Crossfade(targetState = state, label = "screen-state") { screenState ->
        when (screenState) {
            ScreenState.Idle -> IdleScreen(payload, currentTime)
            is ScreenState.AzanAlert -> FullScreenMessage("AZAN ${screenState.prayerName.uppercase()}", Color(0xFF063B22))
            is ScreenState.IqamahCountdown -> FullScreenMessage(
                "Iqamah ${screenState.prayerName}\n${screenState.remainingSeconds / 60}:${(screenState.remainingSeconds % 60).toString().padStart(2, '0')}",
                Color(0xFF102A43)
            )
            is ScreenState.SilentMode -> FullScreenMessage(screenState.message, Color.Black)
        }
    }
}

@Composable
private fun IdleScreen(payload: PrayerResponse, currentTime: String) {
    Column(
        modifier = Modifier.fillMaxSize().background(Color(0xFF071A2D)).padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(payload.masjid.name, color = Color.White, fontSize = 42.sp, fontWeight = FontWeight.Bold)
        Text(currentTime, color = Color(0xFFFFD166), fontSize = 92.sp, fontWeight = FontWeight.ExtraBold)
        Text("${payload.masjid.zoneCode} • ${payload.date.gregorian} • ${payload.date.hijri.orEmpty()}", color = Color.White, fontSize = 28.sp)
        Spacer(Modifier.height(32.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceEvenly) {
            PrayerCell("Imsak/Subuh", "${payload.timeline.imsak ?: "--:--"}\n${payload.timeline.subuh}")
            PrayerCell("Zohor", payload.timeline.zohor)
            PrayerCell("Asar", payload.timeline.asar)
            PrayerCell("Maghrib", payload.timeline.maghrib)
            PrayerCell("Isyak", payload.timeline.isyak)
        }
        Spacer(Modifier.weight(1f))
        Text(payload.announcements.joinToString("   •   ") { it.body ?: it.title.orEmpty() }, color = Color.White, fontSize = 30.sp, maxLines = 1)
    }
}

@Composable
private fun PrayerCell(name: String, time: String) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(name, color = Color(0xFFB8F2E6), fontSize = 28.sp)
        Text(time, color = Color.White, fontSize = 44.sp, fontWeight = FontWeight.Bold)
    }
}

@Composable
private fun FullScreenMessage(message: String, background: Color) {
    Column(
        modifier = Modifier.fillMaxSize().background(background).padding(48.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(message, color = Color.White, fontSize = 76.sp, fontWeight = FontWeight.ExtraBold, style = MaterialTheme.typography.displayLarge)
    }
}
