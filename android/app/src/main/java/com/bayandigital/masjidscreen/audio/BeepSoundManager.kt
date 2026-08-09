package com.bayandigital.masjidscreen.audio

import android.media.ToneGenerator
import android.media.AudioManager

class BeepSoundManager {
    private val tone = ToneGenerator(AudioManager.STREAM_MUSIC, 100)

    fun azanAlert() = tone.startTone(ToneGenerator.TONE_PROP_ACK, 400)
    fun prePrayerAlert() = tone.startTone(ToneGenerator.TONE_PROP_PROMPT, 300)
    fun iqamahAlert() = tone.startTone(ToneGenerator.TONE_CDMA_ALERT_AUTOREDIAL_LITE, 500)
    fun countdownStarted() = tone.startTone(ToneGenerator.TONE_PROP_BEEP, 250)
    fun oneMinuteRemaining() = tone.startTone(ToneGenerator.TONE_CDMA_ALERT_CALL_GUARD, 350)

    fun finalTenSecondDoubleBeep() {
        tone.startTone(ToneGenerator.TONE_PROP_BEEP2, 120)
        android.os.Handler(android.os.Looper.getMainLooper()).postDelayed({
            tone.startTone(ToneGenerator.TONE_PROP_BEEP2, 120)
        }, 180)
    }

    fun release() = tone.release()
}
