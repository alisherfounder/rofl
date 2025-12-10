<?php
header('Content-Type: text/plain');

echo "=== Database Configuration Debug ===\n\n";

echo "Environment Variables:\n";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'NOT SET') . "\n";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'NOT SET') . "\n";
echo "MYSQLUSER: " . (getenv('MYSQLUSER') ?: 'NOT SET') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'SET (hidden)' : 'NOT SET') . "\n";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'NOT SET') . "\n\n";

echo "Fallback Variables:\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? 'SET (hidden)' : 'NOT SET') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: 'NOT SET') . "\n\n";

echo "Resolved Configuration:\n";
require_once 'config.php';
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PORT: " . DB_PORT . "\n\n";

echo "Testing Connection:\n";
try {
    $pdo = getDb();
    echo "✅ Database connection successful!\n";
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL Version: " . $version['version'] . "\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

