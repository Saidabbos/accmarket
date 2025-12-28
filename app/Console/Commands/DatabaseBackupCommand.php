<?php

namespace App\Console\Commands;

use App\Mail\DatabaseBackupReady;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:database
                            {--email= : Specific email to send notification (defaults to admin users)}
                            {--no-email : Skip sending email notification}';

    /**
     * The console command description.
     */
    protected $description = 'Create a database backup and send download link via email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup...');

        try {
            // Generate filename
            $filename = 'backup_' . date('Y-m-d_His') . '.sql.gz';
            $path = 'backups/' . $filename;

            // Create backup directory if it doesn't exist
            Storage::makeDirectory('backups');

            // Generate the SQL dump
            $this->info('Generating SQL dump...');
            $sqlContent = $this->generateDump();

            // Compress and save
            $this->info('Compressing backup...');
            $compressed = gzencode($sqlContent, 9);
            Storage::put($path, $compressed);

            $fileSize = $this->formatBytes(strlen($compressed));
            $this->info("Backup created: {$filename} ({$fileSize})");

            // Generate signed download URL (valid for 24 hours)
            $expiresAt = now()->addHours(24);
            $downloadUrl = URL::temporarySignedRoute(
                'admin.backup.download-file',
                $expiresAt,
                ['filename' => $filename]
            );

            // Send email notification
            if (!$this->option('no-email')) {
                $this->sendEmailNotification($downloadUrl, $filename, $fileSize, $expiresAt);
            }

            // Clean up old backups (keep last 7 days)
            $this->cleanupOldBackups();

            $this->info('Database backup completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Generate SQL dump content.
     */
    private function generateDump(): string
    {
        $output = '';
        $tables = $this->getTables();

        // Header
        $output .= "-- AccMarket Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: " . config('database.connections.mysql.database') . "\n";
        $output .= "-- --------------------------------------------------------\n\n";

        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $output .= $this->dumpTable($table);
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "COMMIT;\n";

        return $output;
    }

    /**
     * Get all tables in the database.
     */
    private function getTables(): array
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        return array_map(fn($table) => $table->$tableKey, $tables);
    }

    /**
     * Dump a single table structure and data.
     */
    private function dumpTable(string $table): string
    {
        $output = '';

        $output .= "-- --------------------------------------------------------\n";
        $output .= "-- Table structure for table `{$table}`\n";
        $output .= "-- --------------------------------------------------------\n\n";

        // Get create table statement
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
        if (!empty($createTable)) {
            $createStatement = $createTable[0]->{'Create Table'} ?? '';
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $createStatement . ";\n\n";
        }

        // Get table data
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            $output .= "-- No data for table `{$table}`\n\n";
            return $output;
        }

        $output .= "-- Dumping data for table `{$table}`\n\n";

        // Get column names
        $columns = array_keys((array) $rows->first());
        $columnList = '`' . implode('`, `', $columns) . '`';

        // Batch insert
        $batchSize = 100;
        $batches = $rows->chunk($batchSize);

        foreach ($batches as $batch) {
            $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";

            $values = [];
            foreach ($batch as $row) {
                $rowValues = [];
                foreach ((array) $row as $value) {
                    if (is_null($value)) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }

            $output .= implode(",\n", $values) . ";\n\n";
        }

        return $output;
    }

    /**
     * Send email notification to admin users.
     */
    private function sendEmailNotification(string $downloadUrl, string $filename, string $fileSize, $expiresAt): void
    {
        $this->info('Sending email notifications...');

        $specificEmail = $this->option('email');

        if ($specificEmail) {
            // Send to specific email
            Mail::to($specificEmail)->send(new DatabaseBackupReady(
                $downloadUrl,
                $filename,
                $fileSize,
                $expiresAt->format('F j, Y \a\t g:i A')
            ));
            $this->info("Email sent to: {$specificEmail}");
        } else {
            // Send to all admin users
            $admins = User::role('admin')->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new DatabaseBackupReady(
                    $downloadUrl,
                    $filename,
                    $fileSize,
                    $expiresAt->format('F j, Y \a\t g:i A')
                ));
                $this->info("Email sent to: {$admin->email}");
            }

            if ($admins->isEmpty()) {
                $this->warn('No admin users found to send notification.');
            }
        }
    }

    /**
     * Clean up old backup files.
     */
    private function cleanupOldBackups(): void
    {
        $this->info('Cleaning up old backups...');

        $files = Storage::files('backups');
        $cutoffDate = now()->subDays(7);
        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);

            if ($lastModified < $cutoffDate->timestamp) {
                Storage::delete($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} old backup(s).");
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
