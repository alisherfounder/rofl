<?php
require_once 'config.php';

function migrateAddType() {
    $pdo = getDb();
    
    $columnExists = false;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM habits LIKE 'type'");
        $columnExists = $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Error checking for column: " . $e->getMessage() . "\n";
        return;
    }
    
    if ($columnExists) {
        echo "Column 'type' already exists. Migration not needed.\n";
        return;
    }
    
    try {
        $pdo->exec("ALTER TABLE habits ADD COLUMN type ENUM('health','fitness','productivity','learning','personal','work','other') DEFAULT 'other'");
        echo "Migration successful: Added 'type' column to habits table.\n";
    } catch (PDOException $e) {
        throw $e;
    }
}

try {
    migrateAddType();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

