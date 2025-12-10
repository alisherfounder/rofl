<?php
require_once 'config.php';

function initDb() {
    $pdo = getDb();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255) DEFAULT NULL,
        status VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS habits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        frequency ENUM('daily','weekly','monthly') NOT NULL,
        type ENUM('health','fitness','productivity','learning','personal','work','other') DEFAULT 'other',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    try {
        $pdo->exec("ALTER TABLE habits ADD COLUMN type ENUM('health','fitness','productivity','learning','personal','work','other') DEFAULT 'other'");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS habit_completions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        habit_id INT NOT NULL,
        completed_at DATE NOT NULL,
        FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
        UNIQUE KEY unique_completion (habit_id, completed_at)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        habit_id INT NOT NULL,
        completion_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
        FOREIGN KEY (completion_id) REFERENCES habit_completions(id) ON DELETE CASCADE
    )");
}

initDb();

echo "Database initialized successfully.";
