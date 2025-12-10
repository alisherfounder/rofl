<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$pdo = getDb();
$stmt = $pdo->prepare("SELECT id, email, name, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: signin.php');
    exit;
}

$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="habits.php" class="header-title">Betterly</a>
            <div class="header-actions">
                <a href="habits.php" class="btn btn-secondary">My Habits</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-secondary">Sign Out</a>
            </div>
        </header>

        <main>
            <div class="form-container">
                <h1 class="page-title text-center" style="margin-bottom: 1.5rem;">Progress</h1>
                
                <?php if ($success): ?>
                    <div class="form-success" role="alert">
                        Profile updated successfully!
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="update_profile.php">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <div class="form-text">Email cannot be changed</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Enter your name">
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <textarea class="form-control" id="status" name="status" rows="3" placeholder="What's on your mind?"><?= htmlspecialchars($user['status'] ?? '') ?></textarea>
                        <div class="form-text">Tell us about yourself or your current status</div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="habits.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>



