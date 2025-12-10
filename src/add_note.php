<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$completion_id = $_REQUEST['completion_id'] ?? 0;

if (!$completion_id) {
    header('Location: habits.php');
    exit;
}

$pdo = getDb();

// Fetch completion details to verify ownership and get habit info
$stmt = $pdo->prepare("
    SELECT hc.*, h.name as habit_name, h.user_id 
    FROM habit_completions hc 
    JOIN habits h ON hc.habit_id = h.id 
    WHERE hc.id = ? AND h.user_id = ?
");
$stmt->execute([$completion_id, $_SESSION['user_id']]);
$completion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$completion) {
    header('Location: habits.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO notes (user_id, habit_id, completion_id, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $completion['habit_id'], $completion_id, $content]);
    }
    
    header('Location: habits.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--color-bg-secondary);
            border-radius: var(--radius);
            border: 1px solid var(--color-border);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            background-color: var(--color-bg);
            transition: border-color 0.2s;
            resize: vertical;
            min-height: 100px;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="habits.php" class="header-title">Betterly</a>
            <div class="header-actions">
                <a href="habits.php" class="btn btn-secondary">My Habits</a>
                <a href="progress.php" class="btn btn-secondary">Progress</a>
                <a href="logout.php" class="btn btn-secondary">Sign Out</a>
            </div>
        </header>

        <main>
            <div class="form-container">
                <h1 class="page-title text-center mb-2">Great Job! 🎉</h1>
                <p class="text-center mb-4">You completed <strong><?= htmlspecialchars($completion['habit_name']) ?></strong>. Want to add a note?</p>
                
                <form method="POST">
                    <input type="hidden" name="completion_id" value="<?= htmlspecialchars($completion_id) ?>">
                    <div class="form-group">
                        <label for="content" class="form-label">Your Note (Optional)</label>
                        <textarea class="form-control" id="content" name="content" placeholder="How did it go? Any thoughts?"></textarea>
                    </div>
                    <div class="header-actions" style="justify-content: center;">
                        <button type="submit" class="btn btn-primary">Save Note</button>
                        <a href="habits.php" class="btn btn-secondary">Skip</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
