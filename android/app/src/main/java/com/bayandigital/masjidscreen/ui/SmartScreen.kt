package com.bayandigital.masjidscreen.ui

import androidx.compose.animation.Crossfade
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.foundation.background
import androidx.compose.foundation.basicMarquee
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
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
import java.time.format.FormatStyle
import kotlin.math.PI
import kotlin.math.cos
import kotlin.math.sin

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
    val accentSoft: Color,
    val text: Color = Color.White,
    val muted: Color
)

private data class PrayerItem(val key: String, val label: String, val time: String)
private data class NextPrayer(val item: PrayerItem, val remainingSeconds: Long)

@Composable
fun SmartScreen(payload: PrayerResponse, currentTime: String, state: ScreenState) {
    val palette = paletteFor(payload.masjid.screenTheme)
    Crossfade(targetState = state, animationSpec = tween(700), label = "screen-state") { screenState ->
        when (screenState) {
            ScreenState.Idle -> DashboardScreen(payload, currentTime, palette)
            is ScreenState.AzanAlert -> FullScreenMessage("AZAN", screenState.prayerName, palette.surface, palette.accent)
            is ScreenState.IqamahCountdown -> FullScreenMessage(
                "IQAMAH ${screenState.prayerName.uppercase()}",
                "${screenState.remainingSeconds / 60}:${(screenState.remainingSeconds % 60).toString().padStart(2, '0')}",
                palette.surface,
                palette.accent
            )
            is ScreenState.SilentMode -> FullScreenMessage("SILENT MODE", screenState.message, Color.Black, palette.accent)
        }
    }
}

@OptIn(ExperimentalFoundationApi::class)
@Composable
private fun DashboardScreen(payload: PrayerResponse, currentTime: String, palette: ScreenPalette) {
    val prayers = prayerItems(payload)
    val nextPrayer = calculateNextPrayer(prayers, currentTime)
    val displayTime = formatClock(currentTime, payload.masjid.timeFormat)
    val contents = payload.announcements.filter { it.type != "ticker" }
    val second = parseTime(currentTime)?.second ?: 0
    val featureIndex = if (contents.isEmpty()) 0 else (second / 10) % contents.size
    val featured = contents.getOrNull(featureIndex) ?: welcomeContent(payload.masjid.name)
    val supporting = contents.filterIndexed { index, _ -> index != featureIndex }.take(2)
    val tickerText = payload.announcements.filter { it.type == "ticker" }
        .joinToString("          ✦          ") { it.body ?: it.title.orEmpty() }
        .ifBlank { "Selamat datang ke ${payload.masjid.name}          ✦          Sila senyapkan telefon bimbit anda" }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd)))
    ) {
        AmbientPattern(palette)
        Column(Modifier.fillMaxSize().padding(start = 28.dp, top = 22.dp, end = 28.dp, bottom = 22.dp)) {
            Masthead(payload, displayTime, palette)
            Spacer(Modifier.height(16.dp))

            Row(Modifier.fillMaxWidth().height(184.dp), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                NextPrayerHero(nextPrayer, palette, Modifier.weight(1.08f).fillMaxHeight())
                PrayerTimeline(
                    prayers = prayers,
                    nextPrayer = nextPrayer,
                    timeFormat = payload.masjid.timeFormat,
                    palette = palette,
                    modifier = Modifier.weight(2.1f).fillMaxHeight()
                )
            }

            Spacer(Modifier.height(16.dp))
            Row(Modifier.fillMaxWidth().weight(1f), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                Crossfade(
                    targetState = featured,
                    animationSpec = tween(700),
                    label = "featured-content",
                    modifier = Modifier.weight(1.75f).fillMaxHeight()
                ) { content -> FeaturedContentCard(content, featureIndex + 1, maxOf(contents.size, 1), palette) }

                Column(Modifier.weight(1f).fillMaxHeight(), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    if (supporting.isEmpty()) {
                        CompactContentCard(
                            AnnouncementDto("announcement", "Adab di rumah Allah", "Jaga kebersihan, rapatkan saf dan hormati jemaah lain."),
                            palette,
                            Modifier.weight(1f).fillMaxWidth()
                        )
                        CompactContentCard(
                            AnnouncementDto("slide", "BayanDigital", "Paparan waktu solat pintar untuk komuniti anda."),
                            palette,
                            Modifier.weight(1f).fillMaxWidth()
                        )
                    } else {
                        supporting.forEach { content ->
                            CompactContentCard(content, palette, Modifier.weight(1f).fillMaxWidth())
                        }
                        if (supporting.size == 1) Spacer(Modifier.weight(1f))
                    }
                }
            }

            Spacer(Modifier.height(14.dp))
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(16.dp))
                    .background(palette.accent)
                    .padding(horizontal = 18.dp, vertical = 11.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    Modifier.clip(RoundedCornerShape(7.dp)).background(palette.background.copy(alpha = .14f)).padding(horizontal = 10.dp, vertical = 5.dp)
                ) {
                    Text("INFO", color = palette.background, fontSize = 12.sp, fontWeight = FontWeight.Black)
                }
                Text(
                    tickerText,
                    modifier = Modifier.weight(1f).padding(horizontal = 15.dp).basicMarquee(iterations = Int.MAX_VALUE),
                    color = palette.background,
                    fontSize = 19.sp,
                    fontWeight = FontWeight.Bold,
                    maxLines = 1
                )
                Text("bayanDigital  ·  v${BuildConfig.VERSION_NAME}", color = palette.background.copy(alpha = .72f), fontSize = 12.sp, fontWeight = FontWeight.Bold)
            }
        }
    }
}

@Composable
private fun Masthead(payload: PrayerResponse, displayTime: String, palette: ScreenPalette) {
    Row(Modifier.fillMaxWidth().height(110.dp), verticalAlignment = Alignment.CenterVertically) {
        Box(
            Modifier.size(58.dp).clip(RoundedCornerShape(18.dp)).background(palette.accent),
            contentAlignment = Alignment.Center
        ) {
            Text("ب", color = palette.background, fontSize = 31.sp, fontWeight = FontWeight.Black)
        }
        Column(Modifier.padding(start = 16.dp).weight(1f)) {
            Text(
                payload.masjid.name,
                color = palette.text,
                fontSize = 32.sp,
                fontWeight = FontWeight.ExtraBold,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(formatDisplayDate(payload.date.gregorian), color = palette.muted, fontSize = 16.sp, fontWeight = FontWeight.Medium)
                Box(Modifier.padding(horizontal = 10.dp).size(4.dp).clip(CircleShape).background(palette.accent))
                Text(payload.date.hijri.orEmpty(), color = palette.accent, fontSize = 16.sp, fontWeight = FontWeight.Bold)
            }
        }
        Column(horizontalAlignment = Alignment.End) {
            Text(displayTime, color = palette.text, fontSize = 46.sp, fontWeight = FontWeight.Black, maxLines = 1)
            Text(
                "${payload.masjid.type.uppercase()}  ·  ${payload.masjid.zoneCode}",
                color = palette.muted,
                fontSize = 13.sp,
                fontWeight = FontWeight.Bold
            )
        }
    }
}

@Composable
private fun NextPrayerHero(nextPrayer: NextPrayer, palette: ScreenPalette, modifier: Modifier) {
    val infinite = rememberInfiniteTransition(label = "next-prayer-pulse")
    val pulse by infinite.animateFloat(
        initialValue = .96f,
        targetValue = 1.04f,
        animationSpec = infiniteRepeatable(tween(1600, easing = FastOutSlowInEasing), RepeatMode.Reverse),
        label = "pulse"
    )
    Box(
        modifier
            .clip(RoundedCornerShape(28.dp))
            .background(Brush.linearGradient(listOf(palette.accent, palette.accentSoft)))
            .padding(22.dp)
    ) {
        Canvas(Modifier.fillMaxSize()) {
            drawCircle(palette.background.copy(alpha = .08f), radius = size.minDimension * .55f, center = Offset(size.width, 0f))
            drawCircle(palette.background.copy(alpha = .06f), radius = size.minDimension * .32f, center = Offset(size.width * .78f, size.height))
        }
        Column(Modifier.fillMaxSize(), verticalArrangement = Arrangement.SpaceBetween) {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text("SOLAT SETERUSNYA", color = palette.background.copy(alpha = .7f), fontSize = 12.sp, fontWeight = FontWeight.Black)
                Box(Modifier.scale(pulse).size(9.dp).clip(CircleShape).background(palette.background))
            }
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.Bottom) {
                Text(nextPrayer.item.label, color = palette.background, fontSize = 39.sp, fontWeight = FontWeight.Black)
                Column(horizontalAlignment = Alignment.End) {
                    Text("DALAM", color = palette.background.copy(alpha = .62f), fontSize = 11.sp, fontWeight = FontWeight.Black)
                    Text(formatCountdown(nextPrayer.remainingSeconds), color = palette.background, fontSize = 25.sp, fontWeight = FontWeight.Black)
                }
            }
            Box(Modifier.fillMaxWidth().height(5.dp).clip(CircleShape).background(palette.background.copy(alpha = .18f))) {
                Box(
                    Modifier.fillMaxWidth(countdownProgress(nextPrayer.remainingSeconds)).fillMaxHeight().clip(CircleShape).background(palette.background.copy(alpha = .72f))
                )
            }
        }
    }
}

@Composable
private fun PrayerTimeline(
    prayers: List<PrayerItem>,
    nextPrayer: NextPrayer,
    timeFormat: String,
    palette: ScreenPalette,
    modifier: Modifier
) {
    Row(
        modifier
            .clip(RoundedCornerShape(28.dp))
            .background(palette.surface.copy(alpha = .9f))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(28.dp))
            .padding(horizontal = 13.dp, vertical = 16.dp),
        horizontalArrangement = Arrangement.spacedBy(7.dp)
    ) {
        prayers.forEach { prayer ->
            val isNext = prayer.key == nextPrayer.item.key
            Column(
                modifier = Modifier
                    .weight(1f)
                    .fillMaxHeight()
                    .clip(RoundedCornerShape(20.dp))
                    .background(if (isNext) palette.accent.copy(alpha = .14f) else Color.Transparent)
                    .border(if (isNext) 1.5.dp else 0.dp, if (isNext) palette.accent.copy(alpha = .55f) else Color.Transparent, RoundedCornerShape(20.dp))
                    .padding(horizontal = 6.dp, vertical = 14.dp),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center
            ) {
                Box(Modifier.size(7.dp).clip(CircleShape).background(if (isNext) palette.accent else palette.muted.copy(alpha = .3f)))
                Spacer(Modifier.height(10.dp))
                Text(prayer.label.uppercase(), color = if (isNext) palette.accent else palette.muted, fontSize = 12.sp, fontWeight = FontWeight.Black)
                Spacer(Modifier.height(5.dp))
                Text(
                    formatPrayerTime(prayer.time, timeFormat),
                    color = palette.text,
                    fontSize = if (timeFormat == "12h") 20.sp else 25.sp,
                    fontWeight = FontWeight.ExtraBold,
                    maxLines = 1
                )
            }
        }
    }
}

@Composable
private fun FeaturedContentCard(content: AnnouncementDto, position: Int, total: Int, palette: ScreenPalette) {
    Box(
        Modifier
            .fillMaxSize()
            .clip(RoundedCornerShape(28.dp))
            .background(Brush.linearGradient(listOf(palette.surfaceAlt, palette.surface)))
            .border(1.dp, palette.text.copy(alpha = .09f), RoundedCornerShape(28.dp))
            .padding(26.dp)
    ) {
        Canvas(Modifier.align(Alignment.BottomEnd).size(190.dp)) { drawIslamicStar(palette.accent.copy(alpha = .08f)) }
        Column(Modifier.fillMaxSize()) {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                ContentBadge(content.type, palette)
                Text("${position.toString().padStart(2, '0')} / ${total.toString().padStart(2, '0')}", color = palette.muted, fontSize = 12.sp, fontWeight = FontWeight.Bold)
            }
            Spacer(Modifier.weight(.35f))
            Text(
                content.title ?: contentDefaultTitle(content.type),
                color = palette.text,
                fontSize = 33.sp,
                lineHeight = 38.sp,
                fontWeight = FontWeight.Black,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            Spacer(Modifier.height(10.dp))
            Text(
                content.body ?: content.mediaPath.orEmpty(),
                color = palette.muted,
                fontSize = 18.sp,
                lineHeight = 26.sp,
                maxLines = 3,
                overflow = TextOverflow.Ellipsis
            )
            Spacer(Modifier.weight(1f))
            Row(horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                repeat(total) { index ->
                    Box(
                        Modifier.width(if (index == position - 1) 24.dp else 7.dp).height(7.dp).clip(CircleShape)
                            .background(if (index == position - 1) palette.accent else palette.muted.copy(alpha = .25f))
                    )
                }
            }
        }
    }
}

@Composable
private fun CompactContentCard(content: AnnouncementDto, palette: ScreenPalette, modifier: Modifier) {
    Column(
        modifier
            .clip(RoundedCornerShape(24.dp))
            .background(palette.surface.copy(alpha = .88f))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(24.dp))
            .padding(18.dp),
        verticalArrangement = Arrangement.Center
    ) {
        ContentBadge(content.type, palette)
        Spacer(Modifier.height(9.dp))
        Text(
            content.title ?: contentDefaultTitle(content.type),
            color = palette.text,
            fontSize = 20.sp,
            lineHeight = 23.sp,
            fontWeight = FontWeight.ExtraBold,
            maxLines = 2,
            overflow = TextOverflow.Ellipsis
        )
        val body = content.body
        if (!body.isNullOrBlank()) {
            Spacer(Modifier.height(5.dp))
            Text(body, color = palette.muted, fontSize = 14.sp, lineHeight = 19.sp, maxLines = 2, overflow = TextOverflow.Ellipsis)
        }
    }
}

@Composable
private fun ContentBadge(type: String, palette: ScreenPalette) {
    Box(Modifier.clip(RoundedCornerShape(8.dp)).background(palette.accent.copy(alpha = .13f)).padding(horizontal = 9.dp, vertical = 5.dp)) {
        Text(contentTypeLabel(type), color = palette.accent, fontSize = 10.sp, fontWeight = FontWeight.Black)
    }
}

@Composable
private fun AmbientPattern(palette: ScreenPalette) {
    Canvas(Modifier.fillMaxSize()) {
        val spacing = 120.dp.toPx()
        var x = -spacing
        while (x < size.width + spacing) {
            var y = -spacing
            while (y < size.height + spacing) {
                drawCircle(palette.accent.copy(alpha = .025f), 18.dp.toPx(), Offset(x, y), style = Stroke(1.dp.toPx()))
                drawLine(palette.accent.copy(alpha = .018f), Offset(x - 32.dp.toPx(), y), Offset(x + 32.dp.toPx(), y), 1.dp.toPx())
                drawLine(palette.accent.copy(alpha = .018f), Offset(x, y - 32.dp.toPx()), Offset(x, y + 32.dp.toPx()), 1.dp.toPx())
                y += spacing
            }
            x += spacing
        }
        drawCircle(palette.accent.copy(alpha = .06f), size.minDimension * .48f, Offset(size.width * .92f, size.height * .08f), style = Stroke(2.dp.toPx()))
    }
}

private fun androidx.compose.ui.graphics.drawscope.DrawScope.drawIslamicStar(color: Color) {
    val center = Offset(size.width / 2f, size.height / 2f)
    val outer = size.minDimension * .43f
    val inner = outer * .48f
    val path = Path()
    repeat(16) { index ->
        val radius = if (index % 2 == 0) outer else inner
        val angle = -PI / 2 + index * PI / 8
        val point = Offset(center.x + cos(angle).toFloat() * radius, center.y + sin(angle).toFloat() * radius)
        if (index == 0) path.moveTo(point.x, point.y) else path.lineTo(point.x, point.y)
    }
    path.close()
    drawPath(path, color)
    drawCircle(color.copy(alpha = .65f), outer * .64f, center, style = Stroke(2.dp.toPx()))
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
    if (parsed.isEmpty()) return NextPrayer(prayers.first(), 0)
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

private fun formatDisplayDate(value: String): String = runCatching {
    LocalDate.parse(value).format(DateTimeFormatter.ofLocalizedDate(FormatStyle.FULL))
}.getOrDefault(value)

private fun formatCountdown(seconds: Long): String {
    val hours = seconds / 3600
    val minutes = (seconds % 3600) / 60
    return if (hours > 0) "${hours}J ${minutes}M" else "${minutes} MINIT"
}

private fun countdownProgress(seconds: Long): Float = (1f - (seconds.coerceAtMost(14_400).toFloat() / 14_400f)).coerceIn(.08f, 1f)

private fun contentTypeLabel(type: String): String = when (type) {
    "slide" -> "MAKLUMAT"
    "image" -> "GALERI"
    else -> "PENGUMUMAN"
}

private fun contentDefaultTitle(type: String): String = when (type) {
    "slide" -> "Maklumat komuniti"
    "image" -> "Galeri masjid"
    else -> "Pengumuman terkini"
}

private fun welcomeContent(name: String) = AnnouncementDto("announcement", "Selamat datang", "Semoga setiap langkah ke $name menjadi amal yang diberkati.")

private fun paletteFor(theme: String): ScreenPalette = when (theme) {
    "midnight" -> ScreenPalette(Color(0xFF040713), Color(0xFF111E43), Color(0xFF111C3A), Color(0xFF192A57), Color(0xFF67E8F9), Color(0xFF8CF0FA), muted = Color(0xFFA8B5D8))
    "sand" -> ScreenPalette(Color(0xFF1E140C), Color(0xFF452C19), Color(0xFF49301E), Color(0xFF603E22), Color(0xFFF6C85F), Color(0xFFFFDA83), muted = Color(0xFFE5C9A5))
    "royal" -> ScreenPalette(Color(0xFF0D061E), Color(0xFF2B115A), Color(0xFF2E155B), Color(0xFF421F79), Color(0xFFF0C75E), Color(0xFFFFDE7E), muted = Color(0xFFD4C2F0))
    else -> ScreenPalette(Color(0xFF04131F), Color(0xFF0A3B34), Color(0xFF0C3D35), Color(0xFF125347), Color(0xFFFFD166), Color(0xFFFFDE8A), muted = Color(0xFFB8D8D0))
}

@Composable
private fun FullScreenMessage(title: String, message: String, background: Color, accent: Color) {
    Box(
        modifier = Modifier.fillMaxSize().background(Brush.radialGradient(listOf(accent.copy(alpha = .38f), background))).padding(48.dp),
        contentAlignment = Alignment.Center
    ) {
        Column(horizontalAlignment = Alignment.CenterHorizontally) {
            Text(title, color = accent, fontSize = 22.sp, fontWeight = FontWeight.Black, letterSpacing = 4.sp)
            Spacer(Modifier.height(18.dp))
            Text(message, color = Color.White, fontSize = 76.sp, fontWeight = FontWeight.ExtraBold, textAlign = TextAlign.Center, style = MaterialTheme.typography.displayLarge)
        }
    }
}
