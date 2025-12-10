<?php
require_once 'config.php';

function getDashboardData($userId) {
    $pdo = getDb();

    $stmt = $pdo->prepare("
        SELECT hc.completed_at, COUNT(*) as count 
        FROM habit_completions hc
        INNER JOIN habits h ON hc.habit_id = h.id
        WHERE h.user_id = ? 
        AND hc.completed_at >= CURDATE() - INTERVAL 90 DAY 
        GROUP BY hc.completed_at
        ORDER BY hc.completed_at ASC
    ");
    $stmt->execute([$userId]);
    $completions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $completionMap = [];
    foreach ($completions as $completion) {
        $completionMap[$completion['completed_at']] = (int)$completion['count'];
    }

    $today = date('Y-m-d');
    $todayCount = $completionMap[$today] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM habits WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalHabits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $days = [];
    $startDate = date('Y-m-d', strtotime('-89 days'));
    $currentDate = $startDate;
    $endDate = date('Y-m-d');
    $maxIterations = 100;
    $iteration = 0;

    while ($currentDate <= $endDate && $iteration < $maxIterations) {
        $days[] = [
            'date' => $currentDate,
            'count' => $completionMap[$currentDate] ?? 0
        ];
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        $iteration++;
    }

    $maxCount = max(array_column($days, 'count'));
    if ($maxCount === 0) {
        $maxCount = 1;
    }

    $chartDays = 30;
    $chartData = [];
    $chartLabels = [];
    for ($i = $chartDays - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chartLabels[] = date('M j', strtotime($date));
        $chartData[] = $completionMap[$date] ?? 0;
    }
    
    return [
        'todayCount' => $todayCount,
        'totalHabits' => $totalHabits,
        'days' => $days,
        'maxCount' => $maxCount,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData
    ];
}

function getIntensityStyle($count, $maxCount) {
    if ($count === 0) return 'background-color: #ebedf0;';
    $intensity = min(($count / max($maxCount, 1)) * 100, 100);
    $opacity = 0.3 + ($intensity / 100) * 0.7;
    return "background-color: rgba(40, 167, 69, $opacity);";
}
