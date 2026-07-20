package com.bayandigital.masjidscreen.network

import com.bayandigital.masjidscreen.data.MasjidSearchResponse
import com.bayandigital.masjidscreen.data.PairingRequestBody
import com.bayandigital.masjidscreen.data.PairingRequestResponse
import com.bayandigital.masjidscreen.data.PairingStatusResponse
import com.bayandigital.masjidscreen.data.PrayerResponse
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Headers
import retrofit2.http.Header
import retrofit2.http.Path
import retrofit2.http.POST
import retrofit2.http.Query

interface PrayerApi {
    @Headers("Accept: application/json")
    @GET("api/v1/masjids/{masjidId}/screen")
    suspend fun screenPayload(
        @Path("masjidId") masjidId: String,
        @Header("Authorization") authorization: String
    ): PrayerResponse

    @Headers("Accept: application/json")
    @GET("api/v1/masjids/search")
    suspend fun searchMasjids(@Query("q") query: String): MasjidSearchResponse

    @Headers("Accept: application/json")
    @POST("api/v1/masjids/{masjidId}/devices/pair")
    suspend fun requestPairing(
        @Path("masjidId") masjidId: String,
        @Body body: PairingRequestBody
    ): PairingRequestResponse

    @Headers("Accept: application/json")
    @GET("api/v1/pairing/{requestId}")
    suspend fun pairingStatus(
        @Path("requestId") requestId: String,
        @Query("code") pairingCode: String
    ): PairingStatusResponse
}
