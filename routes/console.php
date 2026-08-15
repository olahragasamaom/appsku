<?php

use App\Jobs\AutoSubmitExpiredAttempts;
use App\Jobs\ExpireSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new AutoSubmitExpiredAttempts)->everyMinute();
Schedule::job(new ExpireSubscriptions)->hourly();
