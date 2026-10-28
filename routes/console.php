<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hosting: uruchamiaj Laravel Scheduler co minutę. Kopia lokalna codziennie o 02:00.
\Illuminate\Support\Facades\Schedule::command('site:backup')->dailyAt('02:00')->timezone('Europe/Warsaw')->withoutOverlapping(30);
