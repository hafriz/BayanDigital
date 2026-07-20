package com.bayandigital.masjidscreen.data

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PrayerResponse(
    val masjid: MasjidDto,
    val date: DateDto,
    val timeline: PrayerTimelineDto,
    val announcements: List<AnnouncementDto>,
    @SerialName("synced_at") val syncedAt: String
)

@Serializable
data class MasjidDto(
    val id: String,
    val type: String,
    val name: String,
    @SerialName("zone_code") val zoneCode: String,
    @SerialName("iqamah_minutes") val iqamahMinutes: Map<String, Int> = emptyMap(),
    @SerialName("silent_mode_minutes") val silentModeMinutes: Int = 15
)

@Serializable
data class DateDto(val gregorian: String, val hijri: String? = null)

@Serializable
data class PrayerTimelineDto(
    val imsak: String? = null,
    val subuh: String,
    val syuruk: String? = null,
    val zohor: String,
    val asar: String,
    val maghrib: String,
    val isyak: String
)

@Serializable
data class AnnouncementDto(
    val type: String,
    val title: String? = null,
    val body: String? = null,
    @SerialName("media_path") val mediaPath: String? = null
)
