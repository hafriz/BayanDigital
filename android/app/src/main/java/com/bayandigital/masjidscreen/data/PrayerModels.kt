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

@Serializable
data class MasjidSearchResponse(val results: List<MasjidSearchResult>)

@Serializable
data class MasjidSearchResult(
    val id: String,
    val name: String,
    val type: String,
    @SerialName("zone_code") val zoneCode: String
)

@Serializable
data class PairingRequestBody(@SerialName("device_name") val deviceName: String)

@Serializable
data class PairingRequestResponse(
    @SerialName("request_id") val requestId: String,
    @SerialName("pairing_code") val pairingCode: String,
    @SerialName("masjid_id") val masjidId: String,
    @SerialName("masjid_name") val masjidName: String,
    val status: String,
    @SerialName("expires_at") val expiresAt: String
)

@Serializable
data class PairingStatusResponse(
    val status: String,
    val message: String? = null,
    @SerialName("masjid_id") val masjidId: String? = null,
    @SerialName("masjid_name") val masjidName: String? = null,
    @SerialName("device_token") val deviceToken: String? = null
)
