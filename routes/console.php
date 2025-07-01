<?php

use App\Console\Commands\BackupDatabase;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:db', function () {
    Artisan::call(BackupDatabase::class);
})->purpose('Adatbázis mentés');

Schedule::call(function () {
    Artisan::call('backup:db');
})->daily();
