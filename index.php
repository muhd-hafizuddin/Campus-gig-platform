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
    <?php include 'header.php'; ?>
    
    <section class="hero">
        <h1>Connect, Collaborate, Earn on Campus</h1>
        <p>JomBantu is your campus gig platform connecting students who need help with those who can provide services.</p>
        <div class="cta-buttons">
            <a href="register.php" class="cta-button">Get Started</a>
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
