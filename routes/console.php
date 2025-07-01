<?php

use App\Console\Commands\BackupDatabase;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:db', function () {
    Artisan::call(BackupDatabase::class);
})->purpose('Adatbázis mentés');

app(Schedule::class)->call(function () {
    Artisan::call('backup:db');
})->dailyAt('21:30');
