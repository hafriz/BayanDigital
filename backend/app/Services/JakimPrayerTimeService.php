<?php

namespace App\Services;

use App\Models\PrayerTime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class JakimPrayerTimeService
{
    public function ensureMonthCached(string $zoneCode, ?CarbonImmutable $month = null): void
    {
        $month ??= now()->toImmutable();
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();

        if ($this->hasCompleteRange($zoneCode, $start, $end)) {
            return;
        }

        $payload = Http::retry(3, 500)
            ->timeout(20)
            ->acceptJson()
            ->get(rtrim(config('jakim.endpoint'), '/').'/'.strtoupper($zoneCode), [
                'month' => $start->format('m'),
                'year' => $start->format('Y'),
            ])
            ->throw()
            ->json();

        foreach (Arr::get($payload, 'prayers', []) as $row) {
            $date = $start->setDay((int) $row['day'])->toDateString();

            PrayerTime::updateOrCreate(
                ['zone_code' => strtoupper($zoneCode), 'prayer_date' => $date],
                [
                    'hijri_date' => $row['hijri'] ?? null,
                    'times' => [
                        'imsak' => $this->formatTimestamp($row['imsak'] ?? null),
                        'subuh' => $this->formatTimestamp($row['fajr'] ?? null),
                        'syuruk' => $this->formatTimestamp($row['syuruk'] ?? null),
                        'zohor' => $this->formatTimestamp($row['dhuhr'] ?? null),
                        'asar' => $this->formatTimestamp($row['asr'] ?? null),
                        'maghrib' => $this->formatTimestamp($row['maghrib'] ?? null),
                        'isyak' => $this->formatTimestamp($row['isha'] ?? null),
                    ],
                    'fetched_at' => now(),
                ]
            );
        }
    }

    public function today(string $zoneCode, array $offsets = []): PrayerTime
    {
        $this->ensureMonthCached($zoneCode);

        $record = PrayerTime::query()
            ->where('zone_code', strtoupper($zoneCode))
            ->whereDate('prayer_date', today())
            ->firstOrFail();

        $record->times = collect($record->times)->map(
            fn (?string $time, string $name) => $this->applyOffset($time, (int) ($offsets[$name] ?? 0))
        )->all();

        return $record;
    }

    private function hasCompleteRange(string $zoneCode, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return PrayerTime::query()
            ->where('zone_code', strtoupper($zoneCode))
            ->whereBetween('prayer_date', [$start->toDateString(), $end->toDateString()])
            ->count() >= $start->daysInMonth;
    }

    private function applyOffset(?string $time, int $minutes): ?string
    {
        if ($time === null || $minutes === 0) {
            return $time;
        }

        return CarbonImmutable::createFromFormat('H:i:s', $time)->addMinutes($minutes)->format('H:i:s');
    }

    private function formatTimestamp(mixed $timestamp): ?string
    {
        if (! is_numeric($timestamp)) {
            return null;
        }

        return CarbonImmutable::createFromTimestampUTC((int) $timestamp)
            ->setTimezone(config('app.timezone'))
            ->format('H:i:s');
    }
}
