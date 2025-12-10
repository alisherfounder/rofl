<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: habits.php');
    exit;
}

$habit_id = $_POST['habit_id'] ?? 0;

if (!$habit_id) {
    header('Location: habits.php');
    exit;
}

$pdo = getDb();

$stmt = $pdo->prepare("SELECT id FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $_SESSION['user_id']]);
$habit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habit) {
    header('Location: habits.php');
    exit;
}

$stmt = $pdo->prepare("INSERT IGNORE INTO habit_completions (habit_id, completed_at) VALUES (?, CURDATE())");
$stmt->execute([$habit_id]);
$completion_id = $pdo->lastInsertId();

if ($completion_id) {
    header('Location: add_note.php?completion_id=' . $completion_id);
} else {
    // If insertion failed (e.g. already exists), just go back to habits
    header('Location: habits.php');
}
exit;

