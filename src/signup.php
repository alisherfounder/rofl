<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $pdo = getDb();
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: signin.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Email already exists';
            } else {
                $error = 'Registration failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header" style="border-bottom: none; margin-bottom: 2rem;">
            <a href="index.php" class="header-title">Betterly</a>
        </header>

        <main>
            <div class="form-container">
                <h1 class="page-title text-center" style="margin-bottom: 1.5rem;">Sign Up</h1>
                
                <?php if ($error): ?>
                    <div style="background-color: #fee; color: #c33; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password (min 6 characters)" required>
                        <div class="form-text">Password must be at least 6 characters</div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Sign Up</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                    
                    <p style="text-align: center; margin-top: 1.5rem; color: var(--color-text-secondary); font-size: 0.875rem;">
                        Already have an account? <a href="signin.php" style="color: var(--color-primary); text-decoration: none;">Sign In</a>
                    </p>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
