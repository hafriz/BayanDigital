package com.bayandigital.masjidscreen.setup

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
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
fun SetupScreen(
    initialId: String = "",
    isConnecting: Boolean = false,
    errorMessage: String? = null,
    onSave: (String) -> Unit
) {
    var masjidId by remember(initialId) { mutableStateOf(initialId) }

    Column(
        modifier = Modifier.fillMaxSize().background(Color(0xFF071A2D)).padding(48.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text("Masjid Smart Screen Setup", color = Color.White, fontSize = 44.sp, fontWeight = FontWeight.Bold)
        Text("Enter the unique ID from the Laravel registration page.", color = Color(0xFFB8F2E6), fontSize = 24.sp)
        Spacer(Modifier.height(24.dp))
        OutlinedTextField(
            value = masjidId,
            onValueChange = { masjidId = it.uppercase() },
            modifier = Modifier.widthIn(min = 520.dp),
            enabled = !isConnecting,
            singleLine = true,
            label = { Text("Masjid / Surau Unique ID") },
            placeholder = { Text("Example: MSJ-Q8OKLWZI") },
            isError = errorMessage != null,
            supportingText = if (errorMessage != null) {
                { Text(errorMessage, color = Color(0xFFFFB4AB), fontSize = 18.sp) }
            } else null,
            colors = OutlinedTextFieldDefaults.colors(
                focusedTextColor = Color.White,
                unfocusedTextColor = Color.White,
                disabledTextColor = Color(0xFFCBDDD8),
                cursorColor = Color(0xFFFFD166),
                focusedBorderColor = Color(0xFFFFD166),
                unfocusedBorderColor = Color(0xFFB8F2E6),
                errorBorderColor = Color(0xFFFFB4AB),
                focusedLabelColor = Color(0xFFFFD166),
                unfocusedLabelColor = Color(0xFFB8F2E6),
                focusedPlaceholderColor = Color(0xFF86A9A1),
                unfocusedPlaceholderColor = Color(0xFF86A9A1)
            )
        )
        Spacer(Modifier.height(16.dp))
        Button(
            enabled = masjidId.isNotBlank() && !isConnecting,
            onClick = { onSave(masjidId.trim().uppercase()) }
        ) {
            Text(if (isConnecting) "Connecting…" else "Connect Display")
        }
    }
}
