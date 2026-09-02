<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::call(function (): void {
    if (config('session.driver') !== 'database') {
        return;
    }

    $lifetime = (int) config('session.lifetime', 120);

    DB::table(config('session.table', 'sessions'))
        ->where('last_activity', '<=', now()->subMinutes($lifetime)->timestamp)
        ->delete();
})->hourly()->name('delete-expired-database-sessions')->withoutOverlapping();

