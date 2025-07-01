<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:db';
    protected $description = 'Adatbázis mentése napi szinten';

    public function handle()
    {
        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $db = config('database.connections.mysql');

        $command = sprintf(
            '/usr/bin/mysqldump mysqldump -u%s -p%s -h%s %s > %s/%s',
            $db['username'],
            $db['password'],
            $db['host'],
            $db['database'],
            $path,
            $filename
        );

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Sikeres mentés: {$filename}");
        } else {
            $this->error("Mentés sikertelen!");
        }
    }
}
