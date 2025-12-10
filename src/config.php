<?php
define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'mysql');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'mydb');
define('DB_USER', getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'appuser');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: 'apppassword');
define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306');

function getDb() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

session_start();
