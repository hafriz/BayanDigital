package com.bayandigital.masjidscreen.update

import com.bayandigital.masjidscreen.data.AndroidUpdate
import java.io.File
import java.io.IOException
import kotlinx.serialization.json.Json
import okhttp3.OkHttpClient
import okhttp3.Request

class AppUpdater(
    private val baseUrl: String,
    private val latestJsonPath: String = "android/latest.json",
    private val client: OkHttpClient = OkHttpClient.Builder().build(),
    private val json: Json = Json { ignoreUnknownKeys = true }
) {
    private fun resolve(path: String): String =
        if (path.startsWith("http")) path else baseUrl.trimEnd('/') + "/" + path.trimStart('/')

    suspend fun fetchLatest(): AndroidUpdate? = runCatching {
        val request = Request.Builder().url(resolve(latestJsonPath)).build()
        client.newCall(request).execute().use { response ->
            if (!response.isSuccessful) return@use null
            json.decodeFromString<AndroidUpdate>(response.body?.string().orEmpty())
        }
    }.getOrNull()

    suspend fun downloadApk(apkUrl: String, destination: File): File {
        val request = Request.Builder().url(resolve(apkUrl)).build()
        client.newCall(request).execute().use { response ->
            if (!response.isSuccessful) throw IOException("Download failed with HTTP ${response.code}")
            destination.parentFile?.mkdirs()
            destination.outputStream().use { output ->
                response.body?.byteStream()?.copyTo(output)
            }
        }
        return destination
    }
}
