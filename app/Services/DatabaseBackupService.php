<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Generates a MySQL-compatible SQL dump (structure + data) entirely in PHP.
 *
 * This does not shell out to `mysqldump` so it works even in environments
 * where proc_open/exec are disabled (e.g. the app's Docker container).
 */
class DatabaseBackupService
{
    /**
     * Build a full SQL dump of the default database.
     */
    public function dump(): string
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $prefix = $connection->getTablePrefix();
        $pdo = $connection->getPdo();

        $lines = [];
        $lines[] = '-- ------------------------------------------------------------';
        $lines[] = '-- Finance App database backup';
        $lines[] = '-- Host: '.$connection->getConfig('host');
        $lines[] = '-- Database: '.$database;
        $lines[] = '-- Generated at: '.now()->format('Y-m-d H:i:s');
        $lines[] = '-- ------------------------------------------------------------';
        $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $lines[] = 'SET time_zone = "+00:00";';
        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $lines[] = '';

        $tables = $connection->select('SHOW TABLES');
        $tableKey = 'Tables_in_'.$database;

        foreach ($tables as $row) {
            $tableRow = (array) $row;
            $table = $tableRow[$tableKey] ?? array_values($tableRow)[0];
            $table = $prefix.$table;

            // Table structure.
            $create = $connection->selectOne('SHOW CREATE TABLE `'.$table.'`');
            $createValues = (array) $create;
            $createSql = end($createValues);

            $lines[] = '-- ------------------------------------------------------------';
            $lines[] = '-- Table structure for `'.$table.'`';
            $lines[] = 'DROP TABLE IF EXISTS `'.$table.'`;';
            $lines[] = rtrim((string) $createSql, ';').';';
            $lines[] = '';

            // Table data.
            $rows = $connection->select('SELECT * FROM `'.$table.'`');
            if (count($rows) > 0) {
                $lines[] = '--';
                $lines[] = '-- Dumping data for table `'.$table.'`';
                $lines[] = '--';
                $lines[] = 'LOCK TABLES `'.$table.'` WRITE;';

                foreach ($rows as $row) {
                    $data = (array) $row;
                    $columns = array_map(fn ($column) => '`'.$column.'`', array_keys($data));
                    $values = array_map(fn ($value) => $this->escape($pdo, $value), array_values($data));
                    $lines[] = 'INSERT INTO `'.$table.'` ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).');';
                }

                $lines[] = 'UNLOCK TABLES;';
                $lines[] = '';
            }
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $lines[] = '';
        $lines[] = '-- Backup complete';

        return implode("\n", $lines);
    }

    /**
     * Escape a single value for a SQL literal.
     */
    private function escape(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }
}
