<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$data = getDashboardData($_SESSION['user_id']);
$todayCount = $data['todayCount'];
$totalHabits = $data['totalHabits'];
$days = $data['days'];
$maxCount = $data['maxCount'];
$chartLabels = $data['chartLabels'];
$chartData = $data['chartData'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .card {
            background-color: var(--color-bg-secondary);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--color-border);
        }
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 0.5rem;
        }
        .card-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }
        .contribution-grid {
            display: grid;
            grid-template-rows: repeat(7, 1fr);
            grid-auto-flow: column;
            gap: 4px;
        }
        .contribution-cell {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            background-color: #ebedf0;
        }
        .note-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--color-border);
        }
        .note-item:last-child {
            border-bottom: none;
        }
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Your progress at a glance.</p>

            <div class="dashboard-grid">
                <div class="card">
                    <h2 class="card-title">Habits Completed Today</h2>
                    <p class="card-value"><?= $todayCount ?></p>
                </div>
                <div class="card">
                    <h2 class="card-title">Total Habits</h2>
                    <p class="card-value"><?= $totalHabits ?></p>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title">Daily Completions (Last 30 Days)</h2>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <div class="card mt-2">
                <h2 class="card-title">Activity (Last 90 Days)</h2>
                <div class="contribution-grid">
                    <?php foreach ($days as $day): ?>
                        <div class="contribution-cell" style="<?= getIntensityStyle($day['count'], $maxCount) ?>" title="<?= date('M j, Y', strtotime($day['date'])) . ': ' . $day['count'] . ' completions' ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php
            $pdo = getDb();
            $stmt = $pdo->prepare("
                SELECT n.*, h.name as habit_name
                FROM notes n
                JOIN habits h ON n.habit_id = h.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="card mt-2">
                <h2 class="card-title">Recent Notes</h2>
                <?php if (empty($notes)): ?>
                    <p style="color: var(--color-text-secondary);">No notes yet.</p>
                <?php else: ?>
                    <div class="notes-list">
                        <?php foreach ($notes as $note): ?>
                            <div class="note-item">
                                <div class="note-header">
                                    <strong style="color: var(--color-primary);"><?= htmlspecialchars($note['habit_name']) ?></strong>
                                    <span style="color: var(--color-text-secondary); font-size: 0.85rem;"><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></span>
                                </div>
                                <p class="note-content" style="margin-top: 0.5rem; white-space: pre-wrap;"><?= htmlspecialchars($note['content']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        const ctx = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Habits Completed',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: 'var(--color-primary)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: 'var(--color-primary)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            color: 'var(--color-border)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

