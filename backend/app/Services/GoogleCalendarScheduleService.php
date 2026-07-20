<?php

namespace App\Services;

use App\Models\MosqueSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleCalendarScheduleService
{
    /** @return array<int, array<string, mixed>> */
    public function upcoming(MosqueSetting $masjid): array
    {
        $url = trim((string) $masjid->google_calendar_ics_url);
        if ($url === '' || ! $this->isAllowedUrl($url)) {
            return [];
        }

        $cacheKey = 'google-calendar:'.$masjid->id.':'.sha1($url);
        $lastGoodKey = $cacheKey.':last-good';

        try {
            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($url, $lastGoodKey): array {
                $response = Http::accept('text/calendar')->timeout(8)->retry(2, 250)->get($url)->throw();
                $events = $this->parse((string) $response->body());
                Cache::put($lastGoodKey, $events, now()->addDays(2));

                return $events;
            });
        } catch (Throwable) {
            return Cache::get($lastGoodKey, []);
        }
    }

    private function isAllowedUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['calendar.google.com', 'www.google.com'], true);
    }

    /** @return array<int, array<string, mixed>> */
    private function parse(string $calendar): array
    {
        $calendar = preg_replace("/\r?\n[ \t]/", '', $calendar) ?? $calendar;
        preg_match_all('/BEGIN:VEVENT\R(.*?)\REND:VEVENT/s', $calendar, $matches);
        $events = [];

        foreach ($matches[1] ?? [] as $block) {
            $start = $this->eventDate($block, 'DTSTART');
            if (! $start || $start->lt(now()->subDay()) || $start->gt(now()->addDays(45))) {
                continue;
            }

            $title = $this->property($block, 'SUMMARY') ?: 'Aktiviti surau';
            $description = $this->property($block, 'DESCRIPTION');
            $attachment = $this->imageAttachment($block, $description);
            $body = $start->translatedFormat('D, j M Y · g:i A');
            if ($description) {
                $body .= "\n".trim(preg_replace('~https?://\S+~', '', $description) ?? $description);
            }

            $events[] = [
                'type' => 'schedule',
                'title' => $title,
                'body' => trim($body),
                'media_path' => $attachment,
                '_starts_at' => $start->timestamp,
            ];
        }

        usort($events, fn (array $a, array $b): int => $a['_starts_at'] <=> $b['_starts_at']);

        return array_map(function (array $event): array {
            unset($event['_starts_at']);

            return $event;
        }, array_slice($events, 0, 8));
    }

    private function property(string $block, string $name): ?string
    {
        if (! preg_match('/^'.preg_quote($name, '/').'(?:;[^:]*)?:(.*)$/mi', $block, $match)) {
            return null;
        }

        return trim(str_replace(['\\n', '\\,', '\\;'], ["\n", ',', ';'], $match[1]));
    }

    private function eventDate(string $block, string $name): ?CarbonImmutable
    {
        if (! preg_match('/^'.preg_quote($name, '/').'(;[^:]*)?:(.*)$/mi', $block, $match)) {
            return null;
        }

        $parameters = $match[1] ?? '';
        $value = trim($match[2]);
        preg_match('/TZID=([^;:]+)/i', $parameters, $timezoneMatch);
        $timezone = $timezoneMatch[1] ?? config('app.timezone', 'Asia/Kuala_Lumpur');

        try {
            if (preg_match('/^\d{8}$/', $value)) {
                return CarbonImmutable::createFromFormat('Ymd', $value, $timezone)->startOfDay();
            }

            if (str_ends_with($value, 'Z')) {
                return CarbonImmutable::createFromFormat('Ymd\THis\Z', $value, 'UTC')->setTimezone(config('app.timezone'));
            }

            return CarbonImmutable::createFromFormat('Ymd\THis', $value, $timezone);
        } catch (Throwable) {
            return null;
        }
    }

    private function imageAttachment(string $block, ?string $description): ?string
    {
        if (preg_match('/^ATTACH(?:;[^:]*)?:(https?:\/\/\S+)$/mi', $block, $match)) {
            return trim($match[1]);
        }

        if ($description && preg_match('~https?://\S+\.(?:png|jpe?g|webp|gif)(?:\?\S*)?~i', $description, $match)) {
            return rtrim($match[0], '.,;)');
        }

        return null;
    }
}
