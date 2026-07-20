<?php

namespace App\Services;

use App\Models\MosqueSetting;

class DefaultScreenContentService
{
    public function seed(MosqueSetting $masjid): void
    {
        foreach ($this->items() as $item) {
            $masjid->screenContents()->firstOrCreate(
                ['type' => $item['type'], 'title' => $item['title']],
                $item + ['is_active' => true]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function items(): array
    {
        return [
            ['type' => 'announcement', 'title' => 'Kuliah Perdana', 'body' => 'Kuliah selepas Maghrib bersama Ustaz Ahmad. Semua jemaah dan keluarga dijemput hadir.', 'sort_order' => 10],
            ['type' => 'announcement', 'title' => 'Gotong-Royong Komuniti', 'body' => 'Sabtu ini, 8:00 pagi. Peralatan asas dan sarapan disediakan oleh pihak surau.', 'sort_order' => 20],
            ['type' => 'schedule', 'title' => 'Jadual Ustaz Mingguan', 'body' => "Isnin · Ustaz Ahmad · Tafsir Al-Quran\nRabu · Ustaz Firdaus · Fiqh Ibadah\nJumaat · Ustaz Hakim · Hadis", 'sort_order' => 30],
            ['type' => 'schedule', 'title' => 'Kuliah Hujung Minggu', 'body' => "Sabtu 8:30 pagi · Dhuha & Tazkirah\nAhad 9:00 pagi · Kelas Al-Quran Dewasa", 'sort_order' => 40],
            ['type' => 'slide', 'title' => 'Program Ilmu Mingguan', 'body' => 'Hidupkan rumah Allah dengan majlis ilmu untuk semua peringkat umur.', 'media_path' => '/images/screen/program-ilmu.svg', 'sort_order' => 50],
            ['type' => 'slide', 'title' => 'Tabung Pembangunan', 'body' => 'Sumbangan anda membantu penyelenggaraan dan aktiviti komuniti.', 'media_path' => '/images/screen/tabung-komuniti.svg', 'sort_order' => 60],
            ['type' => 'image', 'title' => 'Kelas Al-Quran', 'body' => 'Pendaftaran kelas kanak-kanak dan dewasa kini dibuka.', 'media_path' => '/images/screen/kelas-quran.svg', 'sort_order' => 70],
            ['type' => 'image', 'title' => 'Komuniti Prihatin', 'body' => 'Bersama membina komuniti yang saling membantu dan menyantuni.', 'media_path' => '/images/screen/komuniti-prihatin.svg', 'sort_order' => 80],
            ['type' => 'ticker', 'title' => 'Adab Masjid', 'body' => 'Sila senyapkan telefon bimbit, jaga kebersihan ruang solat dan pastikan anak-anak sentiasa bersama penjaga.', 'sort_order' => 90],
            ['type' => 'ticker', 'title' => 'Hebahan Kuliah', 'body' => 'Kuliah Maghrib berlangsung setiap Isnin, Rabu dan Jumaat. Semua jemaah dijemput hadir dan mengimarahkan majlis ilmu.', 'sort_order' => 100],
            ['type' => 'ticker', 'title' => 'Sumbangan', 'body' => 'Terima kasih atas sumbangan anda kepada tabung operasi, pendidikan dan kebajikan komuniti.', 'sort_order' => 110],
        ];
    }
}
