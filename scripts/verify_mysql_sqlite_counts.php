<?php
$mysql = new PDO('mysql:host=127.0.0.1;port=3306;dbname=infrasph;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sqlite = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $mysqlCount = (int) $mysql->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    $sqliteCount = (int) $sqlite->query('SELECT COUNT(*) FROM "' . str_replace('"', '""', $table) . '"')->fetchColumn();
    $status = $mysqlCount === $sqliteCount ? 'OK' : 'DIFF';
    echo sprintf("%-30s mysql=%-4d sqlite=%-4d %s\n", $table, $mysqlCount, $sqliteCount, $status);
}
