package com.bayandigital.masjidscreen.setup

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.OutlinedButton
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
import com.bayandigital.masjidscreen.data.MasjidSearchResult
import com.bayandigital.masjidscreen.data.PairingRequestResponse

private val Background = Color(0xFF071A2D)
private val Mint = Color(0xFFB8F2E6)
private val Gold = Color(0xFFFFD166)

@Composable
fun SetupScreen(
    appVersion: String,
    isSearching: Boolean,
    errorMessage: String?,
    results: List<MasjidSearchResult>,
    onSearch: (String) -> Unit,
    onSelect: (MasjidSearchResult) -> Unit
) {
    var query by remember { mutableStateOf("") }

    Column(
        modifier = Modifier.fillMaxSize().background(Background).padding(42.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text("Masjid Smart Screen Setup", color = Color.White, fontSize = 42.sp, fontWeight = FontWeight.Bold)
        Text("Search by masjid / surau name or enter its unique ID.", color = Mint, fontSize = 22.sp)
        Spacer(Modifier.height(20.dp))
        Row(verticalAlignment = Alignment.CenterVertically) {
            OutlinedTextField(
                value = query,
                onValueChange = { query = it },
                modifier = Modifier.widthIn(min = 560.dp),
                enabled = !isSearching,
                singleLine = true,
                label = { Text("Masjid / Surau name or Unique ID") },
                placeholder = { Text("Example: Surau Abdullah Soleh") },
                isError = errorMessage != null,
                colors = setupFieldColors()
            )
            Spacer(Modifier.width(12.dp))
            Button(
                enabled = query.trim().length >= 2 && !isSearching,
                onClick = { onSearch(query.trim()) }
            ) {
                Text(if (isSearching) "Searching…" else "Search")
            }
        }
        if (errorMessage != null) {
            Text(errorMessage, color = Color(0xFFFFB4AB), fontSize = 17.sp, modifier = Modifier.padding(top = 10.dp))
        }
        Spacer(Modifier.height(16.dp))
        if (results.isNotEmpty()) {
            Text("Select your registered location", color = Color.White, fontSize = 20.sp, fontWeight = FontWeight.Bold)
            Spacer(Modifier.height(8.dp))
            LazyColumn(
                modifier = Modifier.widthIn(min = 720.dp).heightIn(max = 330.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(results, key = { it.id }) { masjid ->
                    OutlinedButton(
                        onClick = { onSelect(masjid) },
                        modifier = Modifier.fillMaxWidth(),
                        colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White)
                    ) {
                        Column(Modifier.fillMaxWidth().padding(vertical = 5.dp)) {
                            Text(masjid.name, fontSize = 20.sp, fontWeight = FontWeight.Bold)
                            Text("${masjid.type.replaceFirstChar { it.uppercase() }} • ${masjid.zoneCode} • ${masjid.id}", color = Mint, fontSize = 15.sp)
                        }
                    }
                }
            }
        }
        Spacer(Modifier.weight(1f))
        Text("bayanDigital Android TV v$appVersion", color = Color(0xFF86A9A1), fontSize = 15.sp)
    }
}

@Composable
fun PairingScreen(
    appVersion: String,
    pairing: PairingRequestResponse,
    isChecking: Boolean,
    message: String?,
    onCheck: () -> Unit,
    onCancel: () -> Unit
) {
    Column(
        modifier = Modifier.fillMaxSize().background(Background).padding(48.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(pairing.masjidName, color = Color.White, fontSize = 38.sp, fontWeight = FontWeight.Bold)
        Text("Administrator approval required", color = Mint, fontSize = 22.sp)
        Spacer(Modifier.height(28.dp))
        Text("PAIRING CODE", color = Gold, fontSize = 17.sp, fontWeight = FontWeight.Bold)
        Text(pairing.pairingCode, color = Color.White, fontSize = 82.sp, fontWeight = FontWeight.ExtraBold, letterSpacing = 8.sp)
        Text("In the backend, open Masjids → ${pairing.masjidName} → Paired TVs and approve this matching code.", color = Mint, fontSize = 19.sp)
        if (message != null) Text(message, color = Gold, fontSize = 18.sp, modifier = Modifier.padding(top = 14.dp))
        Spacer(Modifier.height(24.dp))
        Row {
            Button(enabled = !isChecking, onClick = onCheck) { Text(if (isChecking) "Checking…" else "Check approval") }
            Spacer(Modifier.width(12.dp))
            OutlinedButton(onClick = onCancel) { Text("Cancel") }
        }
        Spacer(Modifier.height(28.dp))
        Text("Request expires in 15 minutes • bayanDigital v$appVersion", color = Color(0xFF86A9A1), fontSize = 15.sp)
    }
}

@Composable
private fun setupFieldColors() = OutlinedTextFieldDefaults.colors(
    focusedTextColor = Color.White,
    unfocusedTextColor = Color.White,
    disabledTextColor = Color(0xFFCBDDD8),
    cursorColor = Gold,
    focusedBorderColor = Gold,
    unfocusedBorderColor = Mint,
    errorBorderColor = Color(0xFFFFB4AB),
    focusedLabelColor = Gold,
    unfocusedLabelColor = Mint,
    focusedPlaceholderColor = Color(0xFF86A9A1),
    unfocusedPlaceholderColor = Color(0xFF86A9A1)
)
