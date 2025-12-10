<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$habit_id = $_GET['id'] ?? 0;

$pdo = getDb();
$stmt = $pdo->prepare("SELECT id, name FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $_SESSION['user_id']]);
$habit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$habit) {
    header('Location: habits.php');
    exit;
}

$daysRange = $_GET['days'] ?? 30;
$daysRange = in_array($daysRange, [7, 14, 30, 90]) ? (int)$daysRange : 30;

$stmt = $pdo->prepare("
    SELECT completed_at, COUNT(*) as count 
    FROM habit_completions 
    WHERE habit_id = ? 
    AND completed_at >= CURDATE() - INTERVAL ? DAY 
    GROUP BY completed_at
    ORDER BY completed_at ASC
");
$stmt->execute([$habit_id, $daysRange]);
$completions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$completionMap = [];
foreach ($completions as $completion) {
    $completionMap[$completion['completed_at']] = (int)$completion['count'];
}

$daysData = [];
for ($i = $daysRange - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daysData[] = [
        'date' => $date,
        'label' => date('M j', strtotime($date)),
        'count' => $completionMap[$date] ?? 0
    ];
}

$chartLabels = array_column($daysData, 'label');
$chartData = array_column($daysData, 'count');
$maxCount = max($chartData);
if ($maxCount === 0) {
    $maxCount = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin: 2rem 0;
        }
        .chart-controls {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .chart-controls .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.8125rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a href="habits.php" class="navbar-brand mb-0 h1" style="text-decoration: none;">Betterly</a>
            <div>
                <a href="habits.php" class="btn btn-outline-primary btn-sm me-2">My Habits</a>
                <a href="progress.php" class="btn btn-outline-secondary btn-sm me-2">Progress</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Sign Out</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="card-title mb-0"><?= htmlspecialchars($habit['name']) ?></h2>
                            <a href="habits.php" class="btn btn-outline-secondary">Back to Habits</a>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Completion History</h5>
                            <div class="chart-controls">
                                <a href="?id=<?= $habit_id ?>&days=7" class="btn btn-sm <?= $daysRange == 7 ? 'btn-primary' : 'btn-outline-secondary' ?>">7 Days</a>
                                <a href="?id=<?= $habit_id ?>&days=14" class="btn btn-sm <?= $daysRange == 14 ? 'btn-primary' : 'btn-outline-secondary' ?>">14 Days</a>
                                <a href="?id=<?= $habit_id ?>&days=30" class="btn btn-sm <?= $daysRange == 30 ? 'btn-primary' : 'btn-outline-secondary' ?>">30 Days</a>
                                <a href="?id=<?= $habit_id ?>&days=90" class="btn btn-sm <?= $daysRange == 90 ? 'btn-primary' : 'btn-outline-secondary' ?>">90 Days</a>
                            </div>
                        </div>
                        
                        <?php if (array_sum($chartData) === 0): ?>
                            <div class="alert alert-info">
                                No completions recorded in the last <?= $daysRange ?> days.
                            </div>
                        <?php else: ?>
                            <div class="chart-container">
                                <canvas id="completionChart"></canvas>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="mb-3">Summary</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="h4 mb-1"><?= array_sum($chartData) ?></div>
                                                <div class="small text-muted">Total Completions</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="h4 mb-1"><?= round(array_sum($chartData) / $daysRange, 1) ?></div>
                                                <div class="small text-muted">Avg per Day</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="h4 mb-1"><?= $maxCount ?></div>
                                                <div class="small text-muted">Max in a Day</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 pt-3 border-top">
                            <form method="POST" action="mark_done.php" class="d-inline">
                                <input type="hidden" name="habit_id" value="<?= $habit['id'] ?>">
                                <button type="submit" class="btn btn-success">Mark as Done Today</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (array_sum($chartData) > 0): ?>
    <script>
        const ctx = document.getElementById('completionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Completions',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' completion' + (context.parsed.y !== 1 ? 's' : '');
                            }
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
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

