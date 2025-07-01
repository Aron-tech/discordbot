<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:db';

    protected $description = 'Adatbázis mentése napi szinten';

    public function handle()
    {
        $filename = 'backup_'.now()->format('Y-m-d_H-i-s').'.sql';
        $path = storage_path('app/backups');

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $db = config('database.connections.mysql');

        $command = sprintf(
            'mysqldump --defaults-extra-file=%s %s > %s/%s',
            $this->createTempConfigFile($db),
            escapeshellarg($db['database']),
            escapeshellarg($path),
            escapeshellarg($filename)
        );

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Sikeres mentés: {$filename}");
        } else {
            $this->error("Mentés sikertelen! Hiba kód: {$returnVar}");
            $this->error(implode("\n", $output));
        }
    }

    protected function createTempConfigFile(array $db): string
    {
        $configContent = sprintf(
            "[client]\nuser=%s\npassword=%s\nhost=%s",
            $db['username'],
            $db['password'],
            $db['host']
        );

        $tempFile = tempnam(sys_get_temp_dir(), 'mysql_config_');
        file_put_contents($tempFile, $configContent);

        register_shutdown_function(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });

        return $tempFile;
    }
}
