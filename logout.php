<?php
/**
 * Handles user logout.
 * This script destroys the session and redirects the user.
 */

// Always start the session to access session variables.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If you want to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect the user to the homepage after logout.
header("Location: index.php");
exit();
?>
