<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JomBantu - Campus Gig Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="JomBantu Logo">
            <span>JomBantu</span>
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="jobs/browse.php">Browse Jobs</a></li>
                <li><a href="how-it-works.html">How It Works</a></li>
                <li><a href="about.html">About Us</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                <a href="profile.php" class="profile-btn">My Profile</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            <?php endif; ?>
        </div>
    </header>
    
    <section class="hero">
        <h1>Connect, Collaborate, Earn on Campus</h1>
        <p>JomBantu is your campus gig platform connecting students who need help with those who can provide services.</p>
        <div class="cta-buttons">
            <a href="register.html" class="cta-button">Get Started</a>
            <a href="jobs/browse.php" class="cta-button secondary">Browse Jobs</a>
        </div>
    </section>
    
    <section class="features">
        <h2>Why Choose JomBantu?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Find Help Easily</h3>
                <p>Post your tasks and find qualified students to help you complete them.</p>
            </div>
            <div class="feature-card">
                <h3>Earn Money</h3>
                <p>Offer your skills and services to earn extra income while studying.</p>
            </div>
            <div class="feature-card">
                <h3>Campus Verified</h3>
                <p>All users are verified with campus credentials for a safe environment.</p>
            </div>
        </div>
    </section>
    
    <footer>
        <p>&copy; 2025 JomBantu - Campus Gig Platform. All rights reserved.</p>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>
