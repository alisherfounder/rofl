<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Habit</title>
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
                <h1 class="page-title text-center mb-2">Create New Habit</h1>
                <form method="POST" action="store.php">
                    <div class="form-group">
                        <label for="name" class="form-label">Habit Name</label>
                        <input type="text" class="form-control" id="name" name="name" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="health">Health</option>
                            <option value="fitness">Fitness</option>
                            <option value="productivity">Productivity</option>
                            <option value="learning">Learning</option>
                            <option value="personal">Personal</option>
                            <option value="work">Work</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="frequency" class="form-label">Frequency</label>
                        <select class="form-control" id="frequency" name="frequency" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="btn btn-primary">Create Habit</button>
                        <a href="habits.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

