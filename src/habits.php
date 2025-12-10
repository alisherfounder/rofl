<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

function binarySearch($arr, $target) {
    if (empty($arr) || empty($target)) {
        return $arr;
    }
    
    $targetLower = strtolower($target);
    $left = 0;
    $right = count($arr) - 1;
    $firstMatch = -1;
    
    while ($left <= $right) {
        $mid = intval(($left + $right) / 2);
        $midValue = strtolower($arr[$mid]['name']);
        
        if (strpos($midValue, $targetLower) === 0) {
            $firstMatch = $mid;
            $right = $mid - 1;
        } elseif ($midValue < $targetLower) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }
    
    if ($firstMatch === -1) {
        return [];
    }
    
    $results = [];
    $i = $firstMatch;
    while ($i < count($arr) && strpos(strtolower($arr[$i]['name']), $targetLower) === 0) {
        $results[] = $arr[$i];
        $i++;
    }
    
    return $results;
}

$pdo = getDb();
$typeFilter = $_GET['type'] ?? '';
$searchQuery = trim($_GET['search'] ?? '');

$columnExists = false;
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM habits LIKE 'type'");
    $columnExists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
}

if (!$columnExists) {
    try {
        $pdo->exec("ALTER TABLE habits ADD COLUMN type ENUM('health','fitness','productivity','learning','personal','work','other') DEFAULT 'other'");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            error_log("Failed to add type column: " . $e->getMessage());
        }
    }
}

$query = "SELECT id, name, frequency, COALESCE(type, 'other') as type, created_at FROM habits WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($typeFilter && in_array($typeFilter, ['health', 'fitness', 'productivity', 'learning', 'personal', 'work', 'other'])) {
    $query .= " AND COALESCE(type, 'other') = ?";
    $params[] = $typeFilter;
}

$query .= " ORDER BY name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($searchQuery) {
    $habits = binarySearch($habits, $searchQuery);
}

$today = date('Y-m-d');
$completedToday = [];
if (!empty($habits)) {
    $habitIds = array_column($habits, 'id');
    if (!empty($habitIds)) {
        $placeholders = str_repeat('?,', count($habitIds) - 1) . '?';
        $stmt = $pdo->prepare("SELECT habit_id FROM habit_completions WHERE habit_id IN ($placeholders) AND completed_at = ?");
        $stmt->execute(array_merge($habitIds, [$today]));
        $completedToday = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'habit_id');
    }
}

$typeLabels = [
    'health' => 'Health',
    'fitness' => 'Fitness',
    'productivity' => 'Productivity',
    'learning' => 'Learning',
    'personal' => 'Personal',
    'work' => 'Work',
    'other' => 'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Habits</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filters-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-box {
            flex: 1;
            min-width: 200px;
        }
        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            background-color: var(--color-bg);
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            background-color: var(--color-bg-secondary);
            color: var(--color-text);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background-color: #efefef;
        }
        .filter-btn.active {
            background-color: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }
        .filter-btn.filter-health.active {
            background-color: #10b981;
            border-color: #10b981;
        }
        .filter-btn.filter-fitness.active {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .filter-btn.filter-productivity.active {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }
        .filter-btn.filter-learning.active {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .filter-btn.filter-personal.active {
            background-color: #ec4899;
            border-color: #ec4899;
        }
        .filter-btn.filter-work.active {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        .habit-type {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: var(--color-bg-secondary);
            color: var(--color-text-secondary);
            margin-left: 0.5rem;
        }
        .habit-item.type-health {
            border-left: 3px solid #10b981;
        }
        .habit-item.type-fitness {
            border-left: 3px solid #3b82f6;
        }
        .habit-item.type-productivity {
            border-left: 3px solid #f59e0b;
        }
        .habit-item.type-learning {
            border-left: 3px solid #8b5cf6;
        }
        .habit-item.type-personal {
            border-left: 3px solid #ec4899;
        }
        .habit-item.type-work {
            border-left: 3px solid #6366f1;
        }
        .habit-item.type-other {
            border-left: 3px solid var(--color-border);
        }
        .habit-item.type-health .habit-type {
            background-color: #d1fae5;
            color: #065f46;
        }
        .habit-item.type-fitness .habit-type {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .habit-item.type-productivity .habit-type {
            background-color: #fef3c7;
            color: #92400e;
        }
        .habit-item.type-learning .habit-type {
            background-color: #ede9fe;
            color: #5b21b6;
        }
        .habit-item.type-personal .habit-type {
            background-color: #fce7f3;
            color: #9f1239;
        }
        .habit-item.type-work .habit-type {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .habit-item.type-other .habit-type {
            background-color: var(--color-bg-secondary);
            color: var(--color-text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="habits.php" class="header-title">Betterly</a>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="progress.php" class="btn btn-secondary">Progress</a>
                <a href="logout.php" class="btn btn-secondary">Sign Out</a>
            </div>
        </header>

        <main>
            <h1 class="page-title">My Habits</h1>
            <p class="page-subtitle">Track and build your daily routines.</p>

            <div class="page-actions">
                <a href="create.php" class="btn btn-primary">+ New Habit</a>
            </div>

            <div class="filters-container">
                <div class="search-box">
                    <form method="GET" action="habits.php" style="display: flex; gap: 0.5rem;">
                        <input type="text" name="search" placeholder="Search habits..." value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
                        <?php if ($typeFilter): ?>
                            <input type="hidden" name="type" value="<?= htmlspecialchars($typeFilter) ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-secondary" style="padding: 0.75rem 1rem;">Search</button>
                        <?php if ($searchQuery): ?>
                            <a href="habits.php<?= $typeFilter ? '?type=' . htmlspecialchars($typeFilter) : '' ?>" class="btn btn-secondary" style="padding: 0.75rem 1rem; text-decoration: none;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="filter-buttons">
                    <a href="habits.php<?= $searchQuery ? '?search=' . urlencode($searchQuery) : '' ?>" class="filter-btn <?= !$typeFilter ? 'active' : '' ?>">All</a>
                    <?php foreach ($typeLabels as $typeValue => $typeLabel): ?>
                        <a href="habits.php?type=<?= $typeValue ?><?= $searchQuery ? '&search=' . urlencode($searchQuery) : '' ?>" class="filter-btn filter-<?= $typeValue ?> <?= $typeFilter === $typeValue ? 'active' : '' ?>"><?= htmlspecialchars($typeLabel) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($habits)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p><?= $searchQuery || $typeFilter ? 'No habits found matching your filters.' : 'No habits yet. Create your first habit to get started.' ?></p>
                    <?php if (!$searchQuery && !$typeFilter): ?>
                        <a href="create.php" class="btn btn-primary">Create Habit</a>
                    <?php else: ?>
                        <a href="habits.php" class="btn btn-secondary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="habit-list">
                    <?php foreach ($habits as $habit): ?>
                        <?php 
                        $isCompletedToday = in_array($habit['id'], $completedToday);
                        $habitType = $habit['type'] ?? 'other';
                        ?>
                        <div class="habit-item type-<?= htmlspecialchars($habitType) ?>">
                            <div class="habit-name">
                                <?= htmlspecialchars($habit['name']) ?>
                                <span class="habit-type"><?= htmlspecialchars($typeLabels[$habitType]) ?></span>
                            </div>
                            <div class="habit-status <?= $isCompletedToday ? 'done' : 'pending' ?>">
                                <?= $isCompletedToday ? 'Done' : 'Pending' ?>
                            </div>
                            <div class="habit-actions">
                                <?php if (!$isCompletedToday): ?>
                                    <form method="POST" action="mark_done.php" style="display: inline;">
                                        <input type="hidden" name="habit_id" value="<?= $habit['id'] ?>">
                                        <button type="submit" class="btn btn-secondary">Mark Done</button>
                                    </form>
                                <?php endif; ?>
                                <a href="edit.php?id=<?= $habit['id'] ?>" class="btn btn-secondary">Edit</a>
                                <a href="delete.php?id=<?= $habit['id'] ?>" class="btn btn-secondary" onclick="return confirm('Are you sure you want to delete this habit?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
