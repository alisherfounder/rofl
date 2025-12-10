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

$habit_id = $_POST['id'] ?? 0;
$name = trim($_POST['name'] ?? '');
$frequency = $_POST['frequency'] ?? '';
$type = $_POST['type'] ?? 'other';

$validTypes = ['health', 'fitness', 'productivity', 'learning', 'personal', 'work', 'other'];
if (empty($name) || !in_array($frequency, ['daily', 'weekly', 'monthly']) || !in_array($type, $validTypes)) {
    header('Location: habits.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("UPDATE habits SET name = ?, frequency = ?, type = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$name, $frequency, $type, $habit_id, $_SESSION['user_id']]);

header('Location: habits.php');
exit;

