<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: habits.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $pdo = getDb();
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: habits.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header" style="border-bottom: none; margin-bottom: 2rem;">
            <a href="index.php" class="header-title">Betterly</a>
        </header>

        <main>
            <div class="form-container">
                <h1 class="page-title text-center" style="margin-bottom: 1.5rem;">Sign In</h1>
                
                <?php if (isset($_GET['registered'])): ?>
                    <div class="form-success" role="alert">
                        Registration successful. Please sign in.
                    </div>
                <?php endif; ?>
                
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
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                    
                    <p style="text-align: center; margin-top: 1.5rem; color: var(--color-text-secondary); font-size: 0.875rem;">
                        Don't have an account? <a href="signup.php" style="color: var(--color-primary); text-decoration: none;">Sign Up</a>
                    </p>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
