<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Public demo: wipe and reseed the shared demo contractor hourly, so no
// visitor's changes stick around for the next one. Note: this only fires if
// something is actually running Laravel's scheduler in the deployed
// environment (e.g. a `schedule:work` process, or real cron calling
// `schedule:run` every minute) — it does nothing on its own in this dev
// docker-compose setup. Run `php artisan demo:reset` by hand until that's
// wired up for a real deployment.
Schedule::command('demo:reset')->hourly();
