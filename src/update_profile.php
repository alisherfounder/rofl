<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: progress.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$status = trim($_POST['status'] ?? '');

if (strlen($name) > 255) {
    header('Location: progress.php');
    exit;
}

if (strlen($status) > 500) {
    header('Location: progress.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("UPDATE users SET name = ?, status = ? WHERE id = ?");
$stmt->execute([$name ?: null, $status ?: null, $_SESSION['user_id']]);

header('Location: progress.php?success=1');
exit;

