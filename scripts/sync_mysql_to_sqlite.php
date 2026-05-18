<?php
$mysql = new PDO('mysql:host=127.0.0.1;port=3306;dbname=infrasph;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (! file_exists($sqlitePath)) {
    throw new RuntimeException("SQLite file not found: {$sqlitePath}");
}
$sqlite = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$mysqlTables = $mysql->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$sqliteTables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$tables = array_values(array_intersect($sqliteTables, $mysqlTables));

$sqlite->exec('PRAGMA foreign_keys = OFF');
$sqlite->beginTransaction();
try {
    foreach ($tables as $table) {
        $columns = array_map(
            fn ($row) => $row['name'],
            $sqlite->query('PRAGMA table_info("' . str_replace('"', '""', $table) . '")')->fetchAll()
        );
        if ($columns === []) {
            continue;
        }
        $quotedSqliteTable = '"' . str_replace('"', '""', $table) . '"';
        $quotedMysqlTable = '`' . str_replace('`', '``', $table) . '`';
        $quotedSqliteColumns = implode(', ', array_map(fn ($c) => '"' . str_replace('"', '""', $c) . '"', $columns));
        $quotedMysqlColumns = implode(', ', array_map(fn ($c) => '`' . str_replace('`', '``', $c) . '`', $columns));

        $sqlite->exec("DELETE FROM {$quotedSqliteTable}");
        $rows = $mysql->query("SELECT {$quotedMysqlColumns} FROM {$quotedMysqlTable}")->fetchAll();
        if ($rows === []) {
            echo "{$table}: 0\n";
            continue;
        }
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insert = $sqlite->prepare("INSERT INTO {$quotedSqliteTable} ({$quotedSqliteColumns}) VALUES ({$placeholders})");
        foreach ($rows as $row) {
            $insert->execute(array_map(fn ($c) => $row[$c] ?? null, $columns));
        }
        echo "{$table}: " . count($rows) . "\n";
    }
    $sqlite->commit();
} catch (Throwable $e) {
    if ($sqlite->inTransaction()) {
        $sqlite->rollBack();
    }
    throw $e;
} finally {
    $sqlite->exec('PRAGMA foreign_keys = ON');
}
