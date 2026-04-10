<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// mengirim reminder
Schedule::command('app:send-reminder')->dailyAt('20:00');
Schedule::command('app:detect-habit')->dailyAt('20:00');