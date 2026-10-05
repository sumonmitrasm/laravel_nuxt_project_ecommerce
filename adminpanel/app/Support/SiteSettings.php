<?php

namespace App\Support;

use App\Models\Setting;

final class SiteSettings
{
    public static function get(): ?array
    {
        return ContentCache::remember('settings', 'active', function () {
            return Setting::where('status', true)->latest('id')->first()?->toArray();
        });
    }
}
