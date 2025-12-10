<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$frequency = $_POST['frequency'] ?? '';
$type = $_POST['type'] ?? 'other';

$validTypes = ['health', 'fitness', 'productivity', 'learning', 'personal', 'work', 'other'];
if (empty($name) || !in_array($frequency, ['daily', 'weekly', 'monthly']) || !in_array($type, $validTypes)) {
    header('Location: create.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("INSERT INTO habits (user_id, name, frequency, type) VALUES (?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $name, $frequency, $type]);

header('Location: habits.php');
exit;

