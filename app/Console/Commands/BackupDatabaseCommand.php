<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SystemActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'bimeh:backup-database';

    protected $description = 'پشتیبان‌گیری خودکار و فشرده از دیتابیس در مسیر امن storage/app/private/backups';

    public function handle(): int
    {
        $backupDir = storage_path('app/private/backups');
        if (File::isDirectory($backupDir) === false) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now('Asia/Tehran')->format('Y-m-d_H-i-s');
        $fileName = "backup_afagh_bimeh_{$timestamp}.sql.gz";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            $pdo = DB::connection()->getPdo();
            $sql = "-- Afagh Bimeh Automated Database Backup\n";
            $sql .= "-- Date (Asia/Tehran): " . now('Asia/Tehran')->toDateTimeString() . "\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $tableObj) {
                $table = (string) array_values((array) $tableObj)[0];

                $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
                $createSyntax = (array) ($createTableResult[0] ?? []);
                $createSql = (string) ($createSyntax['Create Table'] ?? array_values($createSyntax)[1] ?? '');

                if (strlen($createSql) > 0) {
                    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n";
                }

                foreach (DB::table($table)->cursor() as $row) {
                    $rowArray = (array) $row;
                    $columns = array_map(fn ($col): string => "`{$col}`", array_keys($rowArray));
                    $values = array_map(function ($val) use ($pdo): string {
                        if ($val === null) {
                            return 'NULL';
                        }

                        return $pdo->quote((string) $val);
                    }, array_values($rowArray));

                    $sql .= sprintf(
                        "INSERT INTO `%s` (%s) VALUES (%s);\n",
                        $table,
                        implode(', ', $columns),
                        implode(', ', $values)
                    );
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $compressed = gzencode($sql, 9);
            File::put($filePath, is_string($compressed) ? $compressed : $sql);

            $files = collect(File::files($backupDir))
                ->sortByDesc(fn (\SplFileInfo $file): int => $file->getMTime())
                ->values();

            if ($files->count() > 14) {
                foreach ($files->slice(14) as $oldFile) {
                    File::delete($oldFile->getPathname());
                }
            }

            SystemActivity::create([
                'event_type' => 'cron_job_executed',
                'title'      => 'پشتیبان‌گیری خودکار دیتابیس با موفقیت انجام شد',
                'icon'       => 'app_reports',
                'color'      => 'emerald',
                'details'    => [
                    'description' => "فایل پشتیبان: {$fileName}",
                ],
            ]);

            $this->info("فایل پشتیبان با موفقیت ذخیره شد: {$fileName}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('خطا در پشتیبان‌گیری دیتابیس: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}