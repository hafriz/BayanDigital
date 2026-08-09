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
        val sleep = sleepTime(masjid, timeline) ?: return false
        val wake = wakeTime(masjid, timeline) ?: return false

        return if (sleep > wake) now >= sleep || now < wake else now >= sleep && now < wake
    }

    fun scheduleBoundaries(context: Context, masjid: MasjidDto, timeline: PrayerTimelineDto) {
        val alarmManager = context.getSystemService(AlarmManager::class.java)
        val wakeIntent = boundaryIntent(context, WAKE_REQUEST_CODE, true)
        val sleepIntent = boundaryIntent(context, SLEEP_REQUEST_CODE, false)
        alarmManager.cancel(wakeIntent)
        alarmManager.cancel(sleepIntent)
        if (!masjid.screenSleepEnabled) return

        val wake = wakeTime(masjid, timeline) ?: return
        val sleep = sleepTime(masjid, timeline) ?: return
        schedule(alarmManager, wakeIntent, nextOccurrence(wake))
        schedule(alarmManager, sleepIntent, nextOccurrence(sleep))
    }

    private fun boundaryIntent(context: Context, requestCode: Int, waking: Boolean) =
        PendingIntent.getBroadcast(
            context,
            requestCode,
            Intent(context, DisplayWakeReceiver::class.java).putExtra(EXTRA_WAKING, waking),
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

    private fun schedule(alarmManager: AlarmManager, intent: PendingIntent, triggerMillis: Long) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.S || alarmManager.canScheduleExactAlarms()) {
            alarmManager.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerMillis, intent)
        } else {
            alarmManager.setAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerMillis, intent)
        }
    }

    private fun nextOccurrence(time: LocalTime): Long {
        val now = LocalDateTime.now()
        var boundary = LocalDateTime.of(LocalDate.now(), time)
        if (!boundary.isAfter(now)) boundary = boundary.plusDays(1)
        return boundary.atZone(ZoneId.systemDefault()).toInstant().toEpochMilli()
    }

    private fun sleepTime(masjid: MasjidDto, timeline: PrayerTimelineDto): LocalTime? =
        if (masjid.screenSleepMode == "after_isyak") {
            parseTime(timeline.isyak)?.plusMinutes(masjid.sleepAfterIsyakMinutes.coerceIn(0, 180).toLong())
        } else {
            parseTime(masjid.screenSleepTime)
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
    private const val SLEEP_REQUEST_CODE = 9018
    private const val EXTRA_WAKING = "display_boundary_waking"
}

class DisplayWakeReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        context.startActivity(
            Intent(context, MainActivity::class.java).apply {
                putExtra(EXTRA_SCHEDULED_WAKE, intent.getBooleanExtra("display_boundary_waking", false))
                putExtra(EXTRA_SCHEDULE_CHANGED, true)
                addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
            }
        )
    }

    companion object {
        const val EXTRA_SCHEDULED_WAKE = "scheduled_display_wake"
        const val EXTRA_SCHEDULE_CHANGED = "display_schedule_changed"
    }
}
