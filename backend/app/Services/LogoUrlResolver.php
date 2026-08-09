<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LogoUrlResolver
{
    public function resolve(?string $logo): string
    {
        if (! $logo) {
            return $this->defaultUrl();
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        return Storage::disk('public')->url($logo);
    }

    public function defaultUrl(): string
    {
        return asset(config('branding.default_logo', 'images/bayandigital-logo.svg'));
    }

    public function isLocal(?string $logo): bool
    {
        return filled($logo)
            && ! str_starts_with($logo, 'http://')
            && ! str_starts_with($logo, 'https://');
    }
}
