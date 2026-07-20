package com.bayandigital.masjidscreen.network

import com.bayandigital.masjidscreen.data.PrayerResponse
import retrofit2.http.GET
import retrofit2.http.Path

interface PrayerApi {
    @GET("api/v1/masjids/{masjidId}/screen")
    suspend fun screenPayload(@Path("masjidId") masjidId: String): PrayerResponse
}
