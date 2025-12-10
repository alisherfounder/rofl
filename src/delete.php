<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$habit_id = $_GET['id'] ?? 0;

if (!$habit_id) {
    header('Location: habits.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $_SESSION['user_id']]);

header('Location: habits.php');
exit;

