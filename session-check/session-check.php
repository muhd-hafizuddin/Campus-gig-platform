<?php
session_start();

echo "<h2>Session Check Page</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Variables:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo "<p style='color: green;'>✓ User is logged in as: " . $_SESSION['name'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ User is NOT logged in</p>";
}

echo "<p><a href='session-test.php'>Back to Session Test</a></p>";
echo "<p><a href='/GigPlatform/index.php'>Go to Index Page</a></p>";
?>