<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "customerdb";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully to the database!";
mysqli_close($conn);
?>