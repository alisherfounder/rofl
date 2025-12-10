<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: habits.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Betterly</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .landing-hero {
            margin: 4rem 0 5rem;
        }
        .landing-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }
        .landing-subtitle {
            font-size: 1.375rem;
            color: var(--color-text-secondary);
            line-height: 1.6;
            margin-bottom: 3rem;
            max-width: 600px;
        }
        .landing-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin: 4rem 0;
        }
        .feature-card {
            padding: 1.5rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            background-color: var(--color-bg-secondary);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }
        .feature-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .feature-desc {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            line-height: 1.6;
        }
        .landing-cta {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid var(--color-border);
        }
        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cta-buttons .btn {
            padding: 0.875rem 2rem;
            font-size: 1rem;
        }
        @media (max-width: 640px) {
            .landing-title {
                font-size: 2.5rem;
            }
            .landing-subtitle {
                font-size: 1.125rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="index.php" class="header-title">Betterly</a>
            <div class="header-actions">
                <a href="signin.php" class="btn btn-secondary">Sign In</a>
            </div>
        </header>

        <main>
            <div class="landing-hero">
                <h1 class="landing-title">Build better habits, effortlessly.</h1>
                <p class="landing-subtitle">
                    Track your daily routines, stay consistent, and watch your progress grow. 
                    Simple, focused, and designed for the long run.
                </p>
            </div>

            <div class="landing-features">
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <div class="feature-title">Track Daily</div>
                    <div class="feature-desc">Mark habits complete and build your streak. See your consistency at a glance.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <div class="feature-title">View Progress</div>
                    <div class="feature-desc">Visualize your journey with charts and insights. Understand your patterns.</div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <div class="feature-title">Stay Focused</div>
                    <div class="feature-desc">No distractions, no complexity. Just you and your habits.</div>
                </div>
            </div>

            <div class="landing-cta">
                <div class="cta-buttons">
                    <a href="signup.php" class="btn btn-primary">Get Started</a>
                    <a href="signin.php" class="btn btn-secondary">Sign In</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
