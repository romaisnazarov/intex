<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CleanupStaleUsersJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $staleMinutes = (int) env('CLEANUP_STALE_MINUTES', 1);

    CleanupStaleUsersJob::dispatch($staleMinutes);
})->everyMinute();
