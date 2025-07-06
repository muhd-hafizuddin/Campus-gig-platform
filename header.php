<?php
// We don't need to call session_start() here if the parent file that includes this already has it.
// However, it doesn't hurt to have it, as it will just resume the session if already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <a href="<?php echo isset($is_subdirectory) ? '../index.php' : 'index.php'; ?>" class="logo">
        <img src="<?php echo isset($is_subdirectory) ? '../images/logo.png' : 'images/logo.png'; ?>" alt="JomBantu Logo">
        <span>JomBantu</span>
    </a>
    <nav>
        <ul>
            <li><a href="<?php echo isset($is_subdirectory) ? '../index.php' : 'index.php'; ?>">Home</a></li>
            <li><a href="<?php echo isset($is_subdirectory) ? '../jobs/browse.php' : 'jobs/browse.php'; ?>">Browse Jobs</a></li>
            <li><a href="<?php echo isset($is_subdirectory) ? '../jobs/create.php' : 'jobs/create.php'; ?>">Post a Job</a></li>
        </ul>
    </nav>
    <div class="auth-buttons">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Shows when user IS logged in -->
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href="<?php echo isset($is_subdirectory) ? '../profile.php' : 'profile.php'; ?>" class="profile-btn">My Profile</a>
            <a href="<?php echo isset($is_subdirectory) ? '../logout.php' : 'logout.php'; ?>" class="logout-btn">Logout</a>
        <?php else: ?>
            <!-- Shows when user IS NOT logged in -->
            <a href="<?php echo isset($is_subdirectory) ? '../login.html' : 'login.html'; ?>" class="login-btn">Login</a>
            <a href="<?php echo isset($is_subdirectory) ? '../register.php' : 'register.php'; ?>" class="register-btn">Register</a>
        <?php endif; ?>
    </div>
</header>
