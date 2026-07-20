package com.bayandigital.masjidscreen.setup

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

@Composable
fun SetupScreen(onSave: (String) -> Unit) {
    var masjidId by remember { mutableStateOf("") }

    Column(
        modifier = Modifier.fillMaxSize().background(Color(0xFF071A2D)).padding(48.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text("Masjid Smart Screen Setup", color = Color.White, fontSize = 44.sp, fontWeight = FontWeight.Bold)
        Text("Enter the unique ID from the Laravel registration page.", color = Color(0xFFB8F2E6), fontSize = 24.sp)
        OutlinedTextField(value = masjidId, onValueChange = { masjidId = it.uppercase() }, label = { Text("Masjid / Surau Unique ID") })
        Button(enabled = masjidId.isNotBlank(), onClick = { onSave(masjidId) }) {
            Text("Connect Display")
        }
    }
}
