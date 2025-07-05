<?php
session_start();

// Set a test session variable
$_SESSION['test'] = 'Session is working!';
$_SESSION['loggedin'] = true;
$_SESSION['name'] = 'Test User';

echo "<h2>Session Debug Information</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Variables:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='session-check.php'>Check Session on Another Page</a></p>";
echo "<p><a href='/GigPlatform/index.php'>Go to Index Page</a></p>";
?>