<?php
// We don't need to call session_start() here if the parent file that includes this already has it.
// However, it doesn't hurt to have it, as it will just resume the session if already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <a href="index.php" class="logo">
        <img src="../images/logo.png" alt="JomBantu Logo">
        <span>JomBantu</span>
    </a>
    <nav>
        <ul>
            <li><a href="../index.php">Home</a></li>
            <!-- Note the change from .html to .php -->
            <li><a href="../jobs/browse.php">Browse Jobs</a></li>
            <li><a href="../jobs/create.php">Post a Job</a></li>
        </ul>
    </nav>
    <div class="auth-buttons">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Shows when user IS logged in -->
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href="../profile.php" class="profile-btn">My Profile</a>
            <a href="../logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <!-- Shows when user IS NOT logged in -->
            <a href="../login.html" class="login-btn">Login</a>
            <a href="../register.html" class="register-btn">Register</a>
        <?php endif; ?>
    </div>
</header>
