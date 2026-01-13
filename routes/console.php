<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Penentuan Status "Alpha"
Schedule::command('attendance:generate-alpha')
    ->dailyAt('23:59')
    ->appendOutputTo(storage_path('logs/attendance_automation.log'));

// 2. Update Data Hari Libur Tahunan
// Berjalan setiap tanggal 1 Januari jam 00:01.
Schedule::command('db:seed --class=HolidaySeeder --force')
    ->yearlyOn(1, 1, '00:01')
    ->onOneServer();