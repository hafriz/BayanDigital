package com.bayandigital.masjidscreen.ui

import androidx.compose.animation.Crossfade
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.bayandigital.masjidscreen.BuildConfig
import com.bayandigital.masjidscreen.data.AnnouncementDto
import com.bayandigital.masjidscreen.data.PrayerResponse
import java.time.Duration
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.LocalTime
import java.time.format.DateTimeFormatter

sealed interface ScreenState {
    data object Idle : ScreenState
    data class AzanAlert(val prayerName: String) : ScreenState
    data class IqamahCountdown(val prayerName: String, val remainingSeconds: Int) : ScreenState
    data class SilentMode(val message: String) : ScreenState
}

private data class ScreenPalette(
    val background: Color,
    val backgroundEnd: Color,
    val surface: Color,
    val surfaceAlt: Color,
    val accent: Color,
    val text: Color = Color.White,
    val muted: Color
)

private data class PrayerItem(val key: String, val label: String, val time: String)
private data class NextPrayer(val item: PrayerItem, val remainingSeconds: Long)

@Composable
fun SmartScreen(payload: PrayerResponse, currentTime: String, state: ScreenState) {
    val palette = paletteFor(payload.masjid.screenTheme)
    Crossfade(targetState = state, label = "screen-state") { screenState ->
        when (screenState) {
            ScreenState.Idle -> DashboardScreen(payload, currentTime, palette)
            is ScreenState.AzanAlert -> FullScreenMessage("AZAN ${screenState.prayerName.uppercase()}", palette.surface, palette.accent)
            is ScreenState.IqamahCountdown -> FullScreenMessage(
                "Iqamah ${screenState.prayerName}\n${screenState.remainingSeconds / 60}:${(screenState.remainingSeconds % 60).toString().padStart(2, '0')}",
                palette.surface,
                palette.accent
            )
            is ScreenState.SilentMode -> FullScreenMessage(screenState.message, Color.Black, palette.accent)
        }
    }
}

@Composable
private fun DashboardScreen(payload: PrayerResponse, currentTime: String, palette: ScreenPalette) {
    val prayers = prayerItems(payload)
    val nextPrayer = calculateNextPrayer(prayers, currentTime)
    val displayTime = formatClock(currentTime, payload.masjid.timeFormat)
    val cardContent = payload.announcements.filter { it.type != "ticker" }.take(3)
    val tickerText = payload.announcements.filter { it.type == "ticker" }
        .joinToString("     ◆     ") { it.body ?: it.title.orEmpty() }
        .ifBlank { "Welcome to ${payload.masjid.name}     ◆     Please silence your mobile phone" }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd)))
            .padding(24.dp)
    ) {
        Row(Modifier.fillMaxWidth().height(138.dp), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            DashboardBox(Modifier.weight(1f).fillMaxHeight(), palette.surface, palette) {
                Text(payload.masjid.name, color = palette.text, fontSize = 34.sp, fontWeight = FontWeight.ExtraBold, maxLines = 1, overflow = TextOverflow.Ellipsis)
                Spacer(Modifier.height(7.dp))
                Text("${payload.masjid.type.replaceFirstChar { it.uppercase() }} • ${payload.masjid.zoneCode}", color = palette.accent, fontSize = 18.sp, fontWeight = FontWeight.Bold)
                Text("${payload.date.gregorian}  •  ${payload.date.hijri.orEmpty()}", color = palette.muted, fontSize = 17.sp)
            }
            DashboardBox(Modifier.width(330.dp).fillMaxHeight(), palette.surfaceAlt, palette, highlighted = true) {
                Text("NEXT PRAYER", color = palette.muted, fontSize = 14.sp, fontWeight = FontWeight.Bold)
                Text(nextPrayer.item.label, color = palette.text, fontSize = 29.sp, fontWeight = FontWeight.ExtraBold)
                Text("in ${formatCountdown(nextPrayer.remainingSeconds)}", color = palette.accent, fontSize = 22.sp, fontWeight = FontWeight.Bold)
            }
            DashboardBox(Modifier.width(340.dp).fillMaxHeight(), palette.surface, palette) {
                Text(displayTime, color = palette.accent, fontSize = 52.sp, fontWeight = FontWeight.ExtraBold, maxLines = 1)
                Text(if (payload.masjid.timeFormat == "12h") "12-hour clock" else "24-hour clock", color = palette.muted, fontSize = 14.sp)
            }
        }

        Spacer(Modifier.height(16.dp))
        Row(Modifier.fillMaxWidth().height(150.dp), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
            prayers.forEach { prayer ->
                PrayerCard(
                    item = prayer,
                    displayTime = formatPrayerTime(prayer.time, payload.masjid.timeFormat),
                    isNext = prayer.key == nextPrayer.item.key,
                    palette = palette,
                    modifier = Modifier.weight(1f).fillMaxHeight()
                )
            }
        }

        Spacer(Modifier.height(16.dp))
        Row(Modifier.fillMaxWidth().weight(1f), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
            if (cardContent.isEmpty()) {
                ContentCard(
                    content = AnnouncementDto("announcement", "Welcome", "Semoga ibadah anda diterima Allah SWT"),
                    palette = palette,
                    modifier = Modifier.weight(1f).fillMaxHeight()
                )
            } else {
                cardContent.forEach { content ->
                    ContentCard(content, palette, Modifier.weight(1f).fillMaxHeight())
                }
            }
        }

        Spacer(Modifier.height(14.dp))
        Row(
            modifier = Modifier.fillMaxWidth().clip(RoundedCornerShape(14.dp)).background(palette.accent).padding(horizontal = 18.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text("ANNOUNCEMENT", color = palette.background, fontSize = 13.sp, fontWeight = FontWeight.ExtraBold)
            Text("  •  $tickerText", modifier = Modifier.weight(1f), color = palette.background, fontSize = 20.sp, fontWeight = FontWeight.Bold, maxLines = 1, overflow = TextOverflow.Clip)
            Text("v${BuildConfig.VERSION_NAME}", color = palette.background.copy(alpha = .75f), fontSize = 13.sp)
        }
    }
}

@Composable
private fun DashboardBox(
    modifier: Modifier,
    color: Color,
    palette: ScreenPalette,
    highlighted: Boolean = false,
    content: @Composable ColumnScope.() -> Unit
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(22.dp))
            .background(color)
            .then(if (highlighted) Modifier.border(2.dp, palette.accent, RoundedCornerShape(22.dp)) else Modifier)
            .padding(20.dp),
        verticalArrangement = Arrangement.Center,
        content = content
    )
}

@Composable
private fun PrayerCard(item: PrayerItem, displayTime: String, isNext: Boolean, palette: ScreenPalette, modifier: Modifier) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(20.dp))
            .background(if (isNext) palette.accent else palette.surface)
            .border(1.dp, if (isNext) palette.accent else palette.muted.copy(alpha = .25f), RoundedCornerShape(20.dp))
            .padding(15.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(item.label.uppercase(), color = if (isNext) palette.background else palette.muted, fontSize = 14.sp, fontWeight = FontWeight.ExtraBold)
        Spacer(Modifier.height(7.dp))
        Text(displayTime, color = if (isNext) palette.background else palette.text, fontSize = 29.sp, fontWeight = FontWeight.ExtraBold, maxLines = 1)
        if (isNext) Text("UP NEXT", color = palette.background.copy(alpha = .7f), fontSize = 11.sp, fontWeight = FontWeight.Bold)
    }
}

@Composable
private fun ContentCard(content: AnnouncementDto, palette: ScreenPalette, modifier: Modifier) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(22.dp))
            .background(Brush.verticalGradient(listOf(palette.surfaceAlt, palette.surface)))
            .border(1.dp, palette.muted.copy(alpha = .2f), RoundedCornerShape(22.dp))
            .padding(22.dp)
    ) {
        Text(content.type.uppercase(), color = palette.accent, fontSize = 12.sp, fontWeight = FontWeight.ExtraBold)
        Text(content.title ?: when (content.type) { "slide" -> "Information"; "image" -> "Media"; else -> "Announcement" }, color = palette.text, fontSize = 23.sp, fontWeight = FontWeight.ExtraBold, maxLines = 2, overflow = TextOverflow.Ellipsis)
        Spacer(Modifier.height(8.dp))
        Text(content.body ?: content.mediaPath.orEmpty(), color = palette.muted, fontSize = 17.sp, maxLines = 4, overflow = TextOverflow.Ellipsis)
    }
}

private fun prayerItems(payload: PrayerResponse) = listOf(
    PrayerItem("subuh", "Subuh", payload.timeline.subuh),
    PrayerItem("syuruk", "Syuruk", payload.timeline.syuruk ?: "--:--"),
    PrayerItem("zohor", "Zohor", payload.timeline.zohor),
    PrayerItem("asar", "Asar", payload.timeline.asar),
    PrayerItem("maghrib", "Maghrib", payload.timeline.maghrib),
    PrayerItem("isyak", "Isyak", payload.timeline.isyak)
)

private fun calculateNextPrayer(prayers: List<PrayerItem>, currentTime: String): NextPrayer {
    val now = parseTime(currentTime) ?: LocalTime.now()
    val parsed = prayers.mapNotNull { item -> parseTime(item.time)?.let { item to it } }
    val nextToday = parsed.firstOrNull { (_, time) -> time.isAfter(now) }
    val selected = nextToday ?: parsed.first()
    val targetDate = if (nextToday == null) LocalDate.now().plusDays(1) else LocalDate.now()
    val seconds = Duration.between(LocalDateTime.of(LocalDate.now(), now), LocalDateTime.of(targetDate, selected.second)).seconds.coerceAtLeast(0)
    return NextPrayer(selected.first, seconds)
}

private fun parseTime(value: String): LocalTime? = runCatching {
    LocalTime.parse(value.trim(), if (value.trim().length >= 8) DateTimeFormatter.ofPattern("HH:mm:ss") else DateTimeFormatter.ofPattern("HH:mm"))
}.getOrNull()

private fun formatClock(value: String, format: String): String {
    val time = parseTime(value) ?: LocalTime.now()
    return time.format(DateTimeFormatter.ofPattern(if (format == "12h") "h:mm:ss a" else "HH:mm:ss"))
}

private fun formatPrayerTime(value: String, format: String): String {
    val time = parseTime(value) ?: return "--:--"
    return time.format(DateTimeFormatter.ofPattern(if (format == "12h") "h:mm a" else "HH:mm"))
}

private fun formatCountdown(seconds: Long): String {
    val hours = seconds / 3600
    val minutes = (seconds % 3600) / 60
    return if (hours > 0) "${hours}h ${minutes}m" else "${minutes}m"
}

private fun paletteFor(theme: String): ScreenPalette = when (theme) {
    "midnight" -> ScreenPalette(Color(0xFF050816), Color(0xFF101B3D), Color(0xFF111C3A), Color(0xFF182957), Color(0xFF67E8F9), muted = Color(0xFFA8B5D8))
    "sand" -> ScreenPalette(Color(0xFF21170F), Color(0xFF3A2718), Color(0xFF49301E), Color(0xFF5B3B22), Color(0xFFF6C85F), muted = Color(0xFFE5C9A5))
    "royal" -> ScreenPalette(Color(0xFF100824), Color(0xFF25104D), Color(0xFF2E155B), Color(0xFF3B1C70), Color(0xFFF0C75E), muted = Color(0xFFD4C2F0))
    else -> ScreenPalette(Color(0xFF061626), Color(0xFF0A332D), Color(0xFF0C3D35), Color(0xFF115247), Color(0xFFFFD166), muted = Color(0xFFB8D8D0))
}

@Composable
private fun FullScreenMessage(message: String, background: Color, accent: Color) {
    Box(
        modifier = Modifier.fillMaxSize().background(Brush.radialGradient(listOf(accent.copy(alpha = .4f), background))).padding(48.dp),
        contentAlignment = Alignment.Center
    ) {
        Text(message, color = Color.White, fontSize = 76.sp, fontWeight = FontWeight.ExtraBold, style = MaterialTheme.typography.displayLarge)
    }
}
