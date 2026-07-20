package com.bayandigital.masjidscreen

import android.app.AlarmManager
import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Build
import com.bayandigital.masjidscreen.data.MasjidDto
import com.bayandigital.masjidscreen.data.PrayerTimelineDto
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.LocalTime
import java.time.ZoneId

object DisplayPowerSchedule {
    fun isSleeping(masjid: MasjidDto, timeline: PrayerTimelineDto, currentTime: String): Boolean {
        if (!masjid.screenSleepEnabled) return false
        val now = parseTime(currentTime) ?: LocalTime.now()
        val sleep = parseTime(masjid.screenSleepTime) ?: return false
        val wake = wakeTime(masjid, timeline) ?: return false

        return if (sleep > wake) now >= sleep || now < wake else now >= sleep && now < wake
    }

    fun scheduleWake(context: Context, masjid: MasjidDto, timeline: PrayerTimelineDto) {
        val alarmManager = context.getSystemService(AlarmManager::class.java)
        val pendingIntent = PendingIntent.getBroadcast(
            context,
            WAKE_REQUEST_CODE,
            Intent(context, DisplayWakeReceiver::class.java),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        alarmManager.cancel(pendingIntent)
        if (!masjid.screenSleepEnabled) return

        val wake = wakeTime(masjid, timeline) ?: return
        val now = LocalDateTime.now()
        var trigger = LocalDateTime.of(LocalDate.now(), wake)
        if (!trigger.isAfter(now)) trigger = trigger.plusDays(1)
        val triggerMillis = trigger.atZone(ZoneId.systemDefault()).toInstant().toEpochMilli()

        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.S || alarmManager.canScheduleExactAlarms()) {
            alarmManager.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerMillis, pendingIntent)
        } else {
            alarmManager.setAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerMillis, pendingIntent)
        }
    }

    private fun wakeTime(masjid: MasjidDto, timeline: PrayerTimelineDto): LocalTime? =
        if (masjid.screenWakeMode == "before_subuh") {
            parseTime(timeline.subuh)?.minusMinutes(masjid.wakeBeforeSubuhMinutes.coerceIn(0, 180).toLong())
        } else {
            parseTime(masjid.screenWakeTime)
        }

    private fun parseTime(value: String): LocalTime? = runCatching {
        LocalTime.parse(value.trim().take(5))
    }.getOrNull()

    private const val WAKE_REQUEST_CODE = 9017
}

class DisplayWakeReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        context.startActivity(
            Intent(context, MainActivity::class.java).apply {
                putExtra(EXTRA_SCHEDULED_WAKE, true)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
            }
        )
    }

    companion object {
        const val EXTRA_SCHEDULED_WAKE = "scheduled_display_wake"
    }
}
