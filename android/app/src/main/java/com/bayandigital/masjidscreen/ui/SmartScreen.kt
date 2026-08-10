package com.bayandigital.masjidscreen.ui

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.Crossfade
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.togetherWith
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
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.bayandigital.masjidscreen.BuildConfig
import com.bayandigital.masjidscreen.data.AnnouncementDto
import com.bayandigital.masjidscreen.data.PrayerResponse
import coil.compose.SubcomposeAsyncImage
import java.time.Duration
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.LocalTime
import java.time.Instant
import java.time.ZoneId
import java.time.format.DateTimeFormatter
import java.time.format.FormatStyle
import kotlin.math.PI
import kotlin.math.cos
import kotlin.math.sin
import kotlinx.coroutines.delay

sealed interface ScreenState {
    data object Idle : ScreenState
    data class AzanAlert(val prayerName: String) : ScreenState
    data class IqamahCountdown(val prayerName: String, val remainingSeconds: Int) : ScreenState
    data class SilentMode(val prayerName: String, val remainingSeconds: Int) : ScreenState
}

enum class ScreenConnectionStatus { Syncing, Connected, Offline }

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

private data class PrayerItem(val key: String, val label: String, val time: String, val iqamahDelayMinutes: Int? = null) {
    val iqamahTime: LocalTime?
        get() = iqamahDelayMinutes?.let { delay -> parseTime(time)?.plusMinutes(delay.toLong()) }
}
private data class NextPrayer(
    val item: PrayerItem,
    val prayerRemainingSeconds: Long,
    val iqamahRemainingSeconds: Long?,
    val awaitingIqamah: Boolean = false
)

@Composable
fun SmartScreen(
    payload: PrayerResponse,
    currentTime: String,
    state: ScreenState,
    nearPrayer: Boolean,
    connectionStatus: ScreenConnectionStatus = ScreenConnectionStatus.Connected,
    lastSuccessfulSyncMillis: Long? = null
) {
    val palette = paletteFor(payload.masjid.screenTheme)
    val globalNotice = payload.announcements.firstOrNull { it.type == "global_notice" }
    Crossfade(targetState = state, animationSpec = tween(700), label = "screen-state") { screenState ->
        when (screenState) {
            ScreenState.Idle -> when {
                globalNotice != null -> GlobalNoticeScreen(globalNotice, payload.masjid.name, palette)
                nearPrayer || !payload.masjid.screenRotationEnabled ->
                    DashboardScreen(payload, currentTime, palette, connectionStatus, lastSuccessfulSyncMillis)
                else -> RotationCarousel(payload, currentTime, palette)
            }
            is ScreenState.AzanAlert -> FullScreenMessage("AZAN", screenState.prayerName, palette.surface, palette.accent)
            is ScreenState.IqamahCountdown -> FullScreenMessage(
                "QAMAT ${screenState.prayerName.uppercase()}",
                formatTimer(screenState.remainingSeconds.toLong()),
                palette.surface,
                palette.accent
            )
            is ScreenState.SilentMode -> FullScreenMessage(
                "WAKTU SOLAT ${screenState.prayerName.uppercase()}",
                formatTimer(screenState.remainingSeconds.toLong()),
                Color.Black,
                palette.accent
            )
        }
    }
}

@Composable
private fun GlobalNoticeScreen(notice: AnnouncementDto, masjidName: String, palette: ScreenPalette) {
    Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(Color(0xFF3B0A0A), palette.background)))) {
        AmbientPattern(palette)
        Column(
            Modifier.fillMaxSize().padding(horizontal = 72.dp, vertical = 54.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text("NOTIS PENTING", color = palette.accent, fontSize = 24.sp, fontWeight = FontWeight.Black, letterSpacing = 4.sp)
            Spacer(Modifier.height(24.dp))
            Text(notice.title ?: "Makluman penting", color = Color.White, fontSize = 54.sp, lineHeight = 62.sp, fontWeight = FontWeight.Black, textAlign = TextAlign.Center)
            if (!notice.body.isNullOrBlank()) {
                Spacer(Modifier.height(22.dp))
                Text(notice.body, color = Color(0xFFFFE4E1), fontSize = 27.sp, lineHeight = 38.sp, fontWeight = FontWeight.Medium, textAlign = TextAlign.Center, maxLines = 7, overflow = TextOverflow.Ellipsis)
            }
            Spacer(Modifier.height(34.dp))
            Text(masjidName, color = Color.White.copy(alpha = .65f), fontSize = 17.sp, fontWeight = FontWeight.Bold)
        }
    }
}

private sealed interface RotationView {
    data object Clock : RotationView
    data object Announcements : RotationView
    data object Schedule : RotationView
    data object Donation : RotationView
    data object Slides : RotationView
    data object Gallery : RotationView
}

@Composable
private fun RotationCarousel(payload: PrayerResponse, currentTime: String, palette: ScreenPalette) {
    val views = remember(payload) { buildRotationViews(payload) }
    if (views.isEmpty()) {
        DashboardScreen(payload, currentTime, palette, ScreenConnectionStatus.Connected, null)
        return
    }

    var index by remember(views) { mutableIntStateOf(0) }
    val durationSeconds = (payload.masjid.rotationDurationSeconds ?: payload.masjid.rotationDurationMinutes * 60)
        .coerceIn(1, 3600)
    LaunchedEffect(views, durationSeconds) {
        index = 0
        while (views.size > 1) {
            delay(durationSeconds * 1000L)
            index = (index + 1) % views.size
        }
    }

    AnimatedContent(
        targetState = views[index],
        transitionSpec = {
            (slideInHorizontally { it } + fadeIn(tween(600))) togetherWith
                (slideOutHorizontally { -it } + fadeOut(tween(600)))
        },
        label = "rotation-view"
    ) { view ->
        when (view) {
            RotationView.Clock -> FullscreenClock(payload, currentTime, palette)
            RotationView.Announcements -> FullscreenAnnouncements(payload, palette)
            RotationView.Schedule -> FullscreenPrayerSchedule(payload, palette)
            RotationView.Donation -> FullscreenDonation(payload, palette)
            RotationView.Slides -> FullscreenImageRotation(payload, palette, "slide", "MAKLUMAT")
            RotationView.Gallery -> FullscreenImageRotation(payload, palette, "image", "GALERI")
        }
    }
}

private fun buildRotationViews(payload: PrayerResponse): List<RotationView> {
    val wanted = payload.masjid.rotationViews
    return buildList {
        if ("clock" in wanted) add(RotationView.Clock)
        if ("announcements" in wanted && payload.announcements.any { it.type == "announcement" }) add(RotationView.Announcements)
        if ("schedule" in wanted) add(RotationView.Schedule)
        if ("donation" in wanted &&
            (!payload.masjid.donationQrUrl.isNullOrBlank() || !payload.masjid.donationCaption.isNullOrBlank())
        ) add(RotationView.Donation)
        if ("slides" in wanted && payload.announcements.any { it.type == "slide" }) add(RotationView.Slides)
        if ("gallery" in wanted && payload.announcements.any { it.type == "image" }) add(RotationView.Gallery)
    }
}

@OptIn(ExperimentalFoundationApi::class)
@Composable
private fun DashboardScreen(
    payload: PrayerResponse,
    currentTime: String,
    palette: ScreenPalette,
    connectionStatus: ScreenConnectionStatus,
    lastSuccessfulSyncMillis: Long?
) {
    val prayers = prayerItems(payload)
    val nextPrayer = calculateNextPrayer(prayers, currentTime)
    val displayTime = formatClock(currentTime, payload.masjid.timeFormat)
    val second = parseTime(currentTime)?.second ?: 0
    val featuredItems = payload.announcements.filter { it.type == "global_notice" || it.type == "announcement" }
    val featureIndex = if (featuredItems.isEmpty()) 0 else (second / 10) % featuredItems.size
    val featured = featuredItems.getOrNull(featureIndex) ?: welcomeContent(payload.masjid.name)
    val schedules = payload.announcements.filter { it.type == "schedule" }
    val alternateItems = payload.announcements.filter {
        it.type != "ticker" && it != featured && it.type != "schedule"
    }
    val scheduleOrAlternate = schedules.getOrNull(if (schedules.isEmpty()) 0 else (second / 12) % schedules.size)
        ?: alternateItems.getOrNull(if (alternateItems.isEmpty()) 0 else (second / 12) % alternateItems.size)
        ?: AnnouncementDto("announcement", "Adab di rumah Allah", "Jaga kebersihan, rapatkan saf dan hormati jemaah lain.")
    val tickerItems = payload.announcements.filter { it.type == "ticker" }
        .mapNotNull { (it.body ?: it.title)?.trim()?.takeIf(String::isNotEmpty) }
    var tickerIndex by remember(tickerItems) { mutableIntStateOf(0) }
    LaunchedEffect(tickerItems) {
        tickerIndex = 0
        while (tickerItems.size > 1) {
            delay(12_000)
            tickerIndex = (tickerIndex + 1) % tickerItems.size
        }
    }
    val tickerText = tickerItems.getOrNull(tickerIndex)
        ?: "Selamat datang ke ${payload.masjid.name}     ✦     Sila senyapkan telefon bimbit anda"

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
                ) { content -> FeaturedContentCard(content, featureIndex + 1, maxOf(featuredItems.size, 1), palette) }

                Column(Modifier.weight(1f).fillMaxHeight(), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Crossfade(targetState = scheduleOrAlternate, animationSpec = tween(600), label = "schedule-or-info", modifier = Modifier.weight(1f).fillMaxWidth()) {
                        if (it.type == "schedule") ScheduleCard(it, palette) else AlternateInfoCard(it, palette)
                    }
                    DonationCard(payload, palette, Modifier.weight(1f).fillMaxWidth())
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
                Crossfade(
                    targetState = tickerText,
                    animationSpec = tween(600),
                    label = "ticker-message",
                    modifier = Modifier.weight(1f).padding(horizontal = 15.dp)
                ) { message ->
                    Text(
                        message,
                        modifier = Modifier.fillMaxWidth().basicMarquee(iterations = Int.MAX_VALUE, initialDelayMillis = 900),
                        color = palette.background,
                        fontSize = 19.sp,
                        fontWeight = FontWeight.Bold,
                        maxLines = 1
                    )
                }
                ConnectionIndicator(connectionStatus, lastSuccessfulSyncMillis, payload.masjid.timeFormat, palette)
                Box(Modifier.padding(horizontal = 9.dp).size(3.dp).clip(CircleShape).background(palette.background.copy(alpha = .38f)))
                Text("v${BuildConfig.VERSION_NAME}", color = palette.background.copy(alpha = .72f), fontSize = 12.sp, fontWeight = FontWeight.Bold)
            }
        }
    }
}

@Composable
private fun ConnectionIndicator(
    status: ScreenConnectionStatus,
    lastSuccessfulSyncMillis: Long?,
    timeFormat: String,
    palette: ScreenPalette
) {
    val statusColor = when (status) {
        ScreenConnectionStatus.Connected -> Color(0xFF087A55)
        ScreenConnectionStatus.Syncing -> Color(0xFF8A6500)
        ScreenConnectionStatus.Offline -> Color(0xFFB42318)
    }
    val label = when (status) {
        ScreenConnectionStatus.Connected -> "CONNECTED"
        ScreenConnectionStatus.Syncing -> "SYNCING"
        ScreenConnectionStatus.Offline -> "OFFLINE"
    }
    Row(verticalAlignment = Alignment.CenterVertically) {
        Box(Modifier.size(7.dp).clip(CircleShape).background(statusColor))
        Text("  $label", color = palette.background, fontSize = 11.sp, fontWeight = FontWeight.Black)
        if (lastSuccessfulSyncMillis != null) {
            Text("  ·  ${formatLastSync(lastSuccessfulSyncMillis, timeFormat)}", color = palette.background.copy(alpha = .68f), fontSize = 11.sp, fontWeight = FontWeight.Bold)
        }
    }
}

@Composable
private fun Masthead(payload: PrayerResponse, displayTime: String, palette: ScreenPalette) {
    Row(Modifier.fillMaxWidth().height(110.dp), verticalAlignment = Alignment.CenterVertically) {
        MastheadLogo(payload.masjid.logoUrl, palette)
        Box(Modifier.width(1.dp).height(42.dp).background(palette.text.copy(alpha = .14f)))
        Column(Modifier.padding(start = 20.dp).weight(1f)) {
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
            Text(displayTime, color = palette.text, fontSize = if (payload.masjid.clockStyle == "big") 62.sp else 46.sp, fontWeight = FontWeight.Black, maxLines = 1)
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
private fun MastheadLogo(logoUrl: String?, palette: ScreenPalette) {
    Box(
        Modifier.width(196.dp).height(76.dp).padding(end = 20.dp).clip(RoundedCornerShape(16.dp)),
        contentAlignment = Alignment.CenterStart
    ) {
        if (logoUrl.isNullOrBlank()) {
            DefaultBayanDigitalLogo(palette)
        } else {
            SubcomposeAsyncImage(
                model = logoUrl,
                contentDescription = "Masjid or surau logo",
                modifier = Modifier.fillMaxSize(),
                contentScale = ContentScale.Fit,
                loading = { DefaultBayanDigitalLogo(palette) },
                error = { DefaultBayanDigitalLogo(palette) }
            )
        }
    }
}

@Composable
private fun DefaultBayanDigitalLogo(palette: ScreenPalette) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Text("bayan", color = palette.text, fontSize = 23.sp, fontWeight = FontWeight.SemiBold, letterSpacing = (-1.2).sp)
        Text("Digital", color = palette.accent, fontSize = 23.sp, fontWeight = FontWeight.Black, letterSpacing = (-1.2).sp)
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
                Text(if (nextPrayer.awaitingIqamah) "MENUNGGU IQAMAH" else "SOLAT SETERUSNYA", color = palette.background.copy(alpha = .7f), fontSize = 12.sp, fontWeight = FontWeight.Black)
                Box(Modifier.scale(pulse).size(9.dp).clip(CircleShape).background(palette.background))
            }
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.Bottom) {
                Text(nextPrayer.item.label, color = palette.background, fontSize = 39.sp, fontWeight = FontWeight.Black)
                Column(horizontalAlignment = Alignment.End) {
                    Text(if (nextPrayer.awaitingIqamah) "QAMAT DALAM" else "AZAN DALAM", color = palette.background.copy(alpha = .62f), fontSize = 11.sp, fontWeight = FontWeight.Black)
                    Text(
                        formatTimer(if (nextPrayer.awaitingIqamah) nextPrayer.iqamahRemainingSeconds ?: 0 else nextPrayer.prayerRemainingSeconds),
                        color = palette.background,
                        fontSize = 25.sp,
                        fontWeight = FontWeight.Black
                    )
                }
            }
            nextPrayer.item.iqamahDelayMinutes?.let { delay ->
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    Text("AZAN ${formatPrayerTime(nextPrayer.item.time, "24h")}", color = palette.background.copy(alpha = .78f), fontSize = 13.sp, fontWeight = FontWeight.Bold)
                    Text(
                        "QAMAT +$delay MIN · ${nextPrayer.item.iqamahTime?.format(DateTimeFormatter.ofPattern("HH:mm")) ?: "--:--"} · ${formatTimer(nextPrayer.iqamahRemainingSeconds ?: 0)}",
                        color = palette.background.copy(alpha = .78f),
                        fontSize = 13.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }
            Box(Modifier.fillMaxWidth().height(5.dp).clip(CircleShape).background(palette.background.copy(alpha = .18f))) {
                Box(
                    Modifier.fillMaxWidth(countdownProgress(if (nextPrayer.awaitingIqamah) nextPrayer.iqamahRemainingSeconds ?: 0 else nextPrayer.prayerRemainingSeconds)).fillMaxHeight().clip(CircleShape).background(palette.background.copy(alpha = .72f))
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
private fun ScheduleCard(content: AnnouncementDto, palette: ScreenPalette) {
    Row(
        Modifier.fillMaxSize()
            .clip(RoundedCornerShape(24.dp))
            .background(palette.surface.copy(alpha = .88f))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(24.dp))
            .padding(18.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        Column(Modifier.weight(1f), verticalArrangement = Arrangement.Center) {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                ContentBadge("schedule", palette)
                Text("MINGGU INI", color = palette.muted, fontSize = 9.sp, fontWeight = FontWeight.Black)
            }
            Spacer(Modifier.height(7.dp))
            Text(
                content.title ?: "Jadual Ustaz",
                color = palette.text,
                fontSize = 18.sp,
                lineHeight = 21.sp,
                fontWeight = FontWeight.ExtraBold,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
            Spacer(Modifier.height(5.dp))
            Text(content.body.orEmpty(), color = palette.muted, fontSize = 12.sp, lineHeight = 16.sp, maxLines = 3, overflow = TextOverflow.Ellipsis)
        }
        if (!content.mediaPath.isNullOrBlank()) {
            SubcomposeAsyncImage(
                model = content.mediaPath,
                contentDescription = "Gambar ${content.title ?: "ustaz"}",
                modifier = Modifier.width(112.dp).fillMaxHeight().clip(RoundedCornerShape(18.dp))
                    .border(2.dp, palette.accent.copy(alpha = .65f), RoundedCornerShape(18.dp)),
                contentScale = ContentScale.Crop,
                loading = { VisualPlaceholder(palette) },
                error = { VisualPlaceholder(palette) }
            )
        }
    }
}

@Composable
private fun AlternateInfoCard(content: AnnouncementDto, palette: ScreenPalette) {
    Column(
        Modifier.fillMaxSize()
            .clip(RoundedCornerShape(24.dp))
            .background(palette.surface.copy(alpha = .88f))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(24.dp))
            .padding(18.dp),
        verticalArrangement = Arrangement.Center
    ) {
        ContentBadge(content.type, palette)
        Spacer(Modifier.height(8.dp))
        Text(content.title ?: contentDefaultTitle(content.type), color = palette.text, fontSize = 19.sp, lineHeight = 22.sp, fontWeight = FontWeight.ExtraBold, maxLines = 2, overflow = TextOverflow.Ellipsis)
        if (!content.body.isNullOrBlank()) {
            Spacer(Modifier.height(5.dp))
            Text(content.body, color = palette.muted, fontSize = 13.sp, lineHeight = 17.sp, maxLines = 3, overflow = TextOverflow.Ellipsis)
        }
    }
}

@Composable
private fun VisualContentCard(content: AnnouncementDto, palette: ScreenPalette) {
    Box(
        Modifier.fillMaxSize()
            .clip(RoundedCornerShape(24.dp))
            .background(Brush.linearGradient(listOf(palette.surfaceAlt, palette.surface)))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(24.dp))
    ) {
        if (!content.mediaPath.isNullOrBlank()) {
            SubcomposeAsyncImage(
                model = content.mediaPath,
                contentDescription = content.title,
                modifier = Modifier.fillMaxSize(),
                contentScale = ContentScale.Crop,
                loading = { VisualPlaceholder(palette) },
                error = { VisualPlaceholder(palette) }
            )
        } else {
            VisualPlaceholder(palette)
        }
        Box(Modifier.fillMaxSize().background(Brush.verticalGradient(listOf(Color.Transparent, palette.background.copy(alpha = .92f)))))
        Column(Modifier.align(Alignment.BottomStart).fillMaxWidth().padding(16.dp)) {
            ContentBadge(content.type, palette)
            Spacer(Modifier.height(6.dp))
            Text(
                content.title ?: contentDefaultTitle(content.type),
                color = Color.White,
                fontSize = 18.sp,
                lineHeight = 21.sp,
                fontWeight = FontWeight.ExtraBold,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
        }
    }
}

@Composable
private fun DonationCard(payload: PrayerResponse, palette: ScreenPalette, modifier: Modifier = Modifier) {
    Row(
        modifier.fillMaxSize()
            .clip(RoundedCornerShape(24.dp))
            .background(Brush.linearGradient(listOf(palette.surfaceAlt, palette.surface)))
            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(24.dp))
            .padding(16.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        Box(
            Modifier.fillMaxHeight().width(112.dp).clip(RoundedCornerShape(14.dp)).background(Color.White),
            contentAlignment = Alignment.Center
        ) {
            if (payload.masjid.donationQrUrl.isNullOrBlank()) {
                Text(
                    "QR SUMBANGAN\nBELUM DISEDIAKAN",
                    color = palette.background,
                    fontSize = 10.sp,
                    lineHeight = 14.sp,
                    fontWeight = FontWeight.Black,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.padding(10.dp)
                )
            } else {
                SubcomposeAsyncImage(
                    model = payload.masjid.donationQrUrl,
                    contentDescription = "Kod QR sumbangan untuk ${payload.masjid.name}",
                    modifier = Modifier.fillMaxSize().padding(5.dp),
                    contentScale = ContentScale.Fit,
                    loading = { VisualPlaceholder(palette) },
                    error = { Text("QR SUMBANGAN\nTIDAK DAPAT DIMUAT", color = palette.background, fontSize = 9.sp, textAlign = TextAlign.Center) }
                )
            }
        }
        Column(Modifier.weight(1f), verticalArrangement = Arrangement.Center) {
            ContentBadge("donation", palette)
            Spacer(Modifier.height(7.dp))
            Text(
                payload.masjid.donationCaption?.takeIf { it.isNotBlank() } ?: "Sumbangan untuk masjid",
                color = palette.text,
                fontSize = 17.sp,
                lineHeight = 20.sp,
                fontWeight = FontWeight.ExtraBold,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            payload.masjid.donationAccount?.takeIf { it.isNotBlank() }?.let {
                Spacer(Modifier.height(5.dp))
                Text(it, color = palette.muted, fontSize = 12.sp, lineHeight = 15.sp, maxLines = 2, overflow = TextOverflow.Ellipsis)
            }
        }
    }
}

@Composable
private fun VisualPlaceholder(palette: ScreenPalette) {
    Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(palette.surfaceAlt, palette.backgroundEnd)))) {
        Canvas(Modifier.align(Alignment.CenterEnd).size(150.dp)) { drawIslamicStar(palette.accent.copy(alpha = .13f)) }
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
    PrayerItem("subuh", "Subuh", payload.timeline.subuh, payload.masjid.iqamahMinutes["subuh"] ?: 10),
    PrayerItem("zohor", "Zohor", payload.timeline.zohor, payload.masjid.iqamahMinutes["zohor"] ?: 10),
    PrayerItem("asar", "Asar", payload.timeline.asar, payload.masjid.iqamahMinutes["asar"] ?: 10),
    PrayerItem("maghrib", "Maghrib", payload.timeline.maghrib, payload.masjid.iqamahMinutes["maghrib"] ?: 5),
    PrayerItem("isyak", "Isyak", payload.timeline.isyak, payload.masjid.iqamahMinutes["isyak"] ?: 10)
)

private fun calculateNextPrayer(prayers: List<PrayerItem>, currentTime: String): NextPrayer {
    val now = parseTime(currentTime) ?: LocalTime.now()
    val parsed = prayers.mapNotNull { item -> parseTime(item.time)?.let { item to it } }
    if (parsed.isEmpty()) return NextPrayer(prayers.first(), 0, null)
    parsed.lastOrNull { (item, azan) ->
        val iqamah = item.iqamahTime
        iqamah != null && !now.isBefore(azan) && now.isBefore(iqamah)
    }?.let { (item, _) ->
        val qamatRemaining = Duration.between(now, requireNotNull(item.iqamahTime)).seconds.coerceAtLeast(0)
        return NextPrayer(item, 0, qamatRemaining, awaitingIqamah = true)
    }
    val nextToday = parsed.firstOrNull { (_, time) -> time.isAfter(now) }
    val selected = nextToday ?: parsed.first()
    val targetDate = if (nextToday == null) LocalDate.now().plusDays(1) else LocalDate.now()
    val seconds = Duration.between(LocalDateTime.of(LocalDate.now(), now), LocalDateTime.of(targetDate, selected.second)).seconds.coerceAtLeast(0)
    val iqamahSeconds = selected.first.iqamahDelayMinutes?.let { seconds + it * 60L }
    return NextPrayer(selected.first, seconds, iqamahSeconds)
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

private fun formatLastSync(value: Long, timeFormat: String): String = runCatching {
    val pattern = if (timeFormat == "12h") "h:mm a" else "HH:mm"
    Instant.ofEpochMilli(value).atZone(ZoneId.systemDefault()).format(DateTimeFormatter.ofPattern(pattern))
}.getOrDefault("saved")

private fun formatTimer(seconds: Long): String {
    val safe = seconds.coerceAtLeast(0)
    val hours = safe / 3600
    val minutes = (safe % 3600) / 60
    val remainder = safe % 60
    return if (hours > 0) "%02d:%02d:%02d".format(hours, minutes, remainder)
    else "%02d:%02d".format(minutes, remainder)
}

private fun countdownProgress(seconds: Long): Float = (1f - (seconds.coerceAtMost(14_400).toFloat() / 14_400f)).coerceIn(.08f, 1f)

private fun contentTypeLabel(type: String): String = when (type) {
    "global_notice" -> "NOTIS PENTING"
    "donation" -> "SUMBANGAN"
    "schedule" -> "JADUAL USTAZ"
    "slide" -> "MAKLUMAT"
    "image" -> "GALERI"
    else -> "PENGUMUMAN"
}

private fun contentDefaultTitle(type: String): String = when (type) {
    "global_notice" -> "Notis penting"
    "schedule" -> "Jadual ustaz"
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
private fun FullscreenClock(payload: PrayerResponse, currentTime: String, palette: ScreenPalette) {
    val prayers = prayerItems(payload)
    val nextPrayer = calculateNextPrayer(prayers, currentTime)
    val displayTime = formatClock(currentTime, payload.masjid.timeFormat)
    val big = payload.masjid.clockStyle == "big"

    Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd)))) {
        AmbientPattern(palette)
        Column(
            Modifier.fillMaxSize().padding(40.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                MastheadLogo(payload.masjid.logoUrl, palette)
                Text(
                    payload.masjid.name,
                    color = palette.text,
                    fontSize = 28.sp,
                    fontWeight = FontWeight.ExtraBold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
            Spacer(Modifier.height(24.dp))
            Text(
                displayTime,
                color = palette.text,
                fontSize = if (big) 180.sp else 140.sp,
                fontWeight = FontWeight.Black,
                maxLines = 1,
                textAlign = TextAlign.Center
            )
            Spacer(Modifier.height(16.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(18.dp), verticalAlignment = Alignment.CenterVertically) {
                Text(formatDisplayDate(payload.date.gregorian), color = palette.muted, fontSize = 22.sp, fontWeight = FontWeight.Medium)
                if (!payload.date.hijri.isNullOrBlank()) {
                    Box(Modifier.size(5.dp).clip(CircleShape).background(palette.accent))
                    Text(payload.date.hijri, color = palette.accent, fontSize = 22.sp, fontWeight = FontWeight.Bold)
                }
            }
            Spacer(Modifier.height(42.dp))
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                Text(
                    if (nextPrayer.awaitingIqamah) "MENUNGGU IQAMAH" else "SOLAT SETERUSNYA",
                    color = palette.muted,
                    fontSize = 17.sp,
                    fontWeight = FontWeight.Black
                )
                Text(nextPrayer.item.label, color = palette.accent, fontSize = 17.sp, fontWeight = FontWeight.Black)
                Text(
                    formatTimer(if (nextPrayer.awaitingIqamah) nextPrayer.iqamahRemainingSeconds ?: 0 else nextPrayer.prayerRemainingSeconds),
                    color = palette.text,
                    fontSize = 17.sp,
                    fontWeight = FontWeight.ExtraBold
                )
            }
        }
    }
}

@Composable
private fun FullscreenAnnouncements(payload: PrayerResponse, palette: ScreenPalette) {
    val items = payload.announcements.filter { it.type == "announcement" }
        .ifEmpty { listOf(welcomeContent(payload.masjid.name)) }
    var index by remember(items) { mutableIntStateOf(0) }
    LaunchedEffect(items) {
        index = 0
        while (items.size > 1) {
            delay(15_000)
            index = (index + 1) % items.size
        }
    }

    AnimatedContent(
        targetState = items[index],
        transitionSpec = { fadeIn(tween(500)) togetherWith fadeOut(tween(500)) },
        label = "announcement-rotate"
    ) { content ->
        Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd))).padding(48.dp)) {
            AmbientPattern(palette)
            Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                ContentBadge(content.type, palette)
                Spacer(Modifier.height(22.dp))
                Text(
                    content.title ?: contentDefaultTitle(content.type),
                    color = palette.text,
                    fontSize = 58.sp,
                    lineHeight = 66.sp,
                    fontWeight = FontWeight.Black,
                    textAlign = TextAlign.Center,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                if (!content.body.isNullOrBlank()) {
                    Spacer(Modifier.height(16.dp))
                    Text(
                        content.body,
                        color = palette.muted,
                        fontSize = 28.sp,
                        lineHeight = 38.sp,
                        textAlign = TextAlign.Center,
                        maxLines = 4,
                        overflow = TextOverflow.Ellipsis
                    )
                }
                Spacer(Modifier.height(30.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    repeat(items.size) { i ->
                        Box(
                            Modifier.width(if (i == index) 30.dp else 10.dp).height(10.dp).clip(CircleShape)
                                .background(if (i == index) palette.accent else palette.muted.copy(alpha = .3f))
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun FullscreenPrayerSchedule(payload: PrayerResponse, palette: ScreenPalette) {
    val prayers = prayerItems(payload)
    Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd))).padding(48.dp)) {
        AmbientPattern(palette)
        Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally) {
            Text("JADUAL SOLAT", color = palette.accent, fontSize = 30.sp, fontWeight = FontWeight.Black, letterSpacing = 3.sp)
            Spacer(Modifier.height(26.dp))
            Row(Modifier.fillMaxWidth().weight(1f), horizontalArrangement = Arrangement.spacedBy(18.dp)) {
                prayers.forEach { prayer ->
                    Column(
                        Modifier.weight(1f).fillMaxHeight().clip(RoundedCornerShape(28.dp))
                            .background(palette.surface.copy(alpha = .9f))
                            .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(28.dp)),
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.Center
                    ) {
                        Text(prayer.label.uppercase(), color = palette.accent, fontSize = 26.sp, fontWeight = FontWeight.Black)
                        Spacer(Modifier.height(14.dp))
                        Text(
                            formatPrayerTime(prayer.time, payload.masjid.timeFormat),
                            color = palette.text,
                            fontSize = 44.sp,
                            fontWeight = FontWeight.ExtraBold,
                            maxLines = 1
                        )
                        prayer.iqamahTime?.let {
                            Spacer(Modifier.height(8.dp))
                            Text("QAMAT ${it.format(DateTimeFormatter.ofPattern("HH:mm"))}", color = palette.muted, fontSize = 18.sp, fontWeight = FontWeight.Bold)
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun FullscreenDonation(payload: PrayerResponse, palette: ScreenPalette) {
    Box(Modifier.fillMaxSize().background(Brush.linearGradient(listOf(palette.background, palette.backgroundEnd))).padding(48.dp)) {
        AmbientPattern(palette)
        Row(Modifier.fillMaxSize(), verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(48.dp)) {
            Box(
                Modifier.width(400.dp).fillMaxHeight().clip(RoundedCornerShape(28.dp)).background(Color.White).padding(20.dp),
                contentAlignment = Alignment.Center
            ) {
                if (payload.masjid.donationQrUrl.isNullOrBlank()) {
                    Text(
                        "QR SUMBANGAN\nBELUM DISEDIAKAN",
                        color = palette.background,
                        fontSize = 22.sp,
                        textAlign = TextAlign.Center,
                        fontWeight = FontWeight.Black
                    )
                } else {
                    SubcomposeAsyncImage(
                        model = payload.masjid.donationQrUrl,
                        contentDescription = "Kod QR sumbangan untuk ${payload.masjid.name}",
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Fit,
                        loading = { VisualPlaceholder(palette) },
                        error = { Text("QR TIDAK DAPAT DIMUAT", color = palette.background, textAlign = TextAlign.Center) }
                    )
                }
            }
            Column(Modifier.weight(1f), horizontalAlignment = Alignment.CenterHorizontally) {
                ContentBadge("donation", palette)
                Spacer(Modifier.height(22.dp))
                Text(
                    payload.masjid.donationCaption?.takeIf { it.isNotBlank() } ?: "Sumbangan untuk ${payload.masjid.name}",
                    color = palette.text,
                    fontSize = 42.sp,
                    lineHeight = 50.sp,
                    fontWeight = FontWeight.ExtraBold,
                    textAlign = TextAlign.Center,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                payload.masjid.donationAccount?.takeIf { it.isNotBlank() }?.let {
                    Spacer(Modifier.height(16.dp))
                    Text(it, color = palette.accent, fontSize = 28.sp, fontWeight = FontWeight.Bold, textAlign = TextAlign.Center)
                }
            }
        }
    }
}

@Composable
private fun FullscreenImageRotation(
    payload: PrayerResponse,
    palette: ScreenPalette,
    contentType: String,
    heading: String
) {
    val items = payload.announcements.filter { it.type == contentType }
    if (items.isEmpty()) return
    var index by remember(items) { mutableIntStateOf(0) }
    LaunchedEffect(items) {
        index = 0
        while (items.size > 1) {
            delay(12_000)
            index = (index + 1) % items.size
        }
    }

    AnimatedContent(
        targetState = items[index],
        transitionSpec = { fadeIn(tween(600)) togetherWith fadeOut(tween(600)) },
        label = "$contentType-rotate"
    ) { content ->
        Box(Modifier.fillMaxSize().background(palette.background).padding(40.dp)) {
            AmbientPattern(palette)
            Box(
                Modifier.fillMaxSize().clip(RoundedCornerShape(28.dp))
                    .background(palette.surface.copy(alpha = .9f))
                    .border(1.dp, palette.text.copy(alpha = .08f), RoundedCornerShape(28.dp))
            ) {
                if (!content.mediaPath.isNullOrBlank()) {
                    SubcomposeAsyncImage(
                        model = content.mediaPath,
                        contentDescription = content.title,
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Fit,
                        loading = { VisualPlaceholder(palette) },
                        error = { VisualPlaceholder(palette) }
                    )
                } else {
                    VisualPlaceholder(palette)
                }
                Column(Modifier.align(Alignment.TopStart).padding(18.dp)) {
                    Box(
                        Modifier.clip(RoundedCornerShape(8.dp))
                            .background(palette.background.copy(alpha = .85f))
                            .padding(horizontal = 12.dp, vertical = 7.dp)
                    ) {
                        Text(heading, color = palette.accent, fontSize = 14.sp, fontWeight = FontWeight.Black, letterSpacing = 2.sp)
                    }
                }
                Row(
                    Modifier.align(Alignment.TopEnd).padding(22.dp),
                    horizontalArrangement = Arrangement.spacedBy(7.dp)
                ) {
                    repeat(items.size) { i ->
                        Box(
                            Modifier.width(if (i == index) 22.dp else 8.dp).height(8.dp).clip(CircleShape)
                                .background(if (i == index) palette.accent else Color.White.copy(alpha = .45f))
                        )
                    }
                }
                if (!content.title.isNullOrBlank() || !content.body.isNullOrBlank()) {
                    Column(
                        Modifier.align(Alignment.BottomStart).fillMaxWidth()
                            .background(Brush.verticalGradient(listOf(Color.Transparent, palette.background.copy(alpha = .96f))))
                            .padding(26.dp)
                    ) {
                        if (!content.title.isNullOrBlank()) {
                            Text(content.title, color = Color.White, fontSize = 30.sp, lineHeight = 36.sp, fontWeight = FontWeight.ExtraBold, maxLines = 2, overflow = TextOverflow.Ellipsis)
                        }
                        if (!content.body.isNullOrBlank()) {
                            Spacer(Modifier.height(6.dp))
                            Text(content.body, color = Color.White.copy(alpha = .82f), fontSize = 17.sp, lineHeight = 24.sp, maxLines = 3, overflow = TextOverflow.Ellipsis)
                        }
                    }
                }
            }
        }
    }
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
