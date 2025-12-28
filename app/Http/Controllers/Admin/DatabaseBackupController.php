<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    /**
     * Download database dump as SQL file.
     */
    public function download(Request $request): StreamedResponse
    {
        $filename = 'accmarket_backup_' . date('Y-m-d_His') . '.sql';

        return response()->streamDownload(function () {
            $this->generateDump();
        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate SQL dump output.
     */
    private function generateDump(): void
    {
        $tables = $this->getTables();

        // Output header
        echo "-- AccMarket Database Backup\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Database: " . config('database.connections.mysql.database') . "\n";
        echo "-- --------------------------------------------------------\n\n";

        echo "SET FOREIGN_KEY_CHECKS=0;\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET AUTOCOMMIT = 0;\n";
        echo "START TRANSACTION;\n";
        echo "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $this->dumpTable($table);
        }

        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        echo "COMMIT;\n";
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
    private function dumpTable(string $table): void
    {
        echo "-- --------------------------------------------------------\n";
        echo "-- Table structure for table `{$table}`\n";
        echo "-- --------------------------------------------------------\n\n";

        // Get create table statement
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
        if (!empty($createTable)) {
            $createStatement = $createTable[0]->{'Create Table'} ?? '';
            echo "DROP TABLE IF EXISTS `{$table}`;\n";
            echo $createStatement . ";\n\n";
        }

        // Get table data
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            echo "-- No data for table `{$table}`\n\n";
            return;
        }

        echo "-- Dumping data for table `{$table}`\n\n";

        // Get column names
        $columns = array_keys((array) $rows->first());
        $columnList = '`' . implode('`, `', $columns) . '`';

        // Batch insert for better performance
        $batchSize = 100;
        $batches = $rows->chunk($batchSize);

        foreach ($batches as $batch) {
            echo "INSERT INTO `{$table}` ({$columnList}) VALUES\n";

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

            echo implode(",\n", $values) . ";\n\n";
        }
    }

    /**
     * Download database dump as compressed gzip file.
     */
    public function downloadCompressed(Request $request): StreamedResponse
    {
        $filename = 'accmarket_backup_' . date('Y-m-d_His') . '.sql.gz';

        return response()->streamDownload(function () {
            $gzHandle = gzopen('php://output', 'wb9');

            ob_start(function ($buffer) use ($gzHandle) {
                gzwrite($gzHandle, $buffer);
                return '';
            });

            $this->generateDump();

            ob_end_flush();
            gzclose($gzHandle);
        }, $filename, [
            'Content-Type' => 'application/gzip',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download a stored backup file (for signed URL downloads from cron).
     */
    public function downloadFile(Request $request, string $filename): StreamedResponse
    {
        // Validate filename to prevent directory traversal
        $filename = basename($filename);
        $path = 'backups/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::download($path, $filename, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * List available backup files.
     */
    public function listBackups(Request $request): array
    {
        $files = Storage::files('backups');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatBytes(Storage::size($file)),
                'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
            ];
        }

        // Sort by date descending
        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
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
