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
    
    <main class="container">
        <div class="hero-section" style="text-align:center;margin-top:4rem;">
            <h1 style="font-size:2.8rem;font-weight:700;color:#15395a;margin-bottom:1.5rem;">Connect, Collaborate, Earn on Campus</h1>
            <p style="font-size:1.3rem;color:#444;margin-bottom:2.5rem;">JomBantu is your campus gig platform connecting students who need help with those who can provide services.</p>
        </div>
        <section style="margin-top:4rem;">
            <h2 style="text-align:center;font-size:2rem;font-weight:600;margin-bottom:2.5rem;">Why Choose JomBantu?</h2>
            <div class="why-choose-grid">
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
    </main>
    
    <footer>
        <p>&copy; 2025 JomBantu - Campus Gig Platform. All rights reserved.</p>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>
