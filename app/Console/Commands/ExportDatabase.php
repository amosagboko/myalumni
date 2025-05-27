<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDatabase extends Command
{
    protected $signature = 'db:export {--path=storage/backups}';
    protected $description = 'Export the database to a SQL file';

    public function handle()
    {
        $path = $this->option('path');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $filename = $path . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tables = array_map(function($table) {
            return array_values((array) $table)[0];
        }, $tables);
        
        $content = '';
        
        // Get database name
        $database = DB::connection()->getDatabaseName();
        $content .= "-- Database: `{$database}`\n\n";
        
        foreach ($tables as $table) {
            $this->info("Exporting table: {$table}");
            
            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
            $content .= "\n-- Table structure for table `{$table}`\n\n";
            $content .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $content .= $createTable . ";\n\n";
            
            // Get table data
            $rows = DB::table($table)->get();
            if (count($rows) > 0) {
                $content .= "-- Data for table `{$table}`\n\n";
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $content .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $content .= "\n";
            }
        }
        
        File::put($filename, $content);
        $this->info("Database exported successfully to: {$filename}");
        
        return Command::SUCCESS;
    }
} 