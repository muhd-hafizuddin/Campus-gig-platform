<?php
// --- BARE-BONES VERSION FOR DEBUGGING ---

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Always start the session at the very top
session_start();

// Function to display a custom message box (replaces alert())
function showMessageBox($message, $redirectUrl = '') {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Message</title>
        <style>
            body { font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f2f5; }
            .message-box { background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); text-align: center; max-width: 400px; width: 90%; }
            .message-box h2 { color: #333; margin-bottom: 20px; }
            .message-box p { color: #555; margin-bottom: 30px; line-height: 1.6; }
            .message-box button { background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
            .message-box button:hover { background-color: #0056b3; }
        </style>
    </head>
    <body>
        <div class='message-box'>
            <h2>Notification</h2>
            <p>" . htmlspecialchars($message) . "</p>
            <button onclick='";
            if ($redirectUrl) {
                echo "window.location.href=\"" . htmlspecialchars($redirectUrl) . "\"";
            } else {
                echo "history.back()"; // Go back if no specific redirect URL
            }
            echo "'>OK</button>
        </div>
    </body>
    </html>";
    exit();
}


// --- 1. Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "customerdb";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Immediately stop if the connection fails
if (!$conn) {
    error_log("Database Connection Failed: " . mysqli_connect_error());
    showMessageBox('A database connection error occurred. Please try again later.', 'index.html');
}

// --- 2. Registration Logic ---
if (isset($_POST['register'])) {
    error_log("DEBUG: Register form submitted.");
    showMessageBox('DEBUG: Register form submitted.'); // Debugging output

    $name = $_POST['fullName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phoneNum'];

    if ($_POST['password'] !== $_POST['confirmPassword']) {
        showMessageBox('Passwords do not match!', 'register.html');
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $check_sql = "SELECT email FROM user WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if (!$check_stmt) {
        error_log("Prepare failed for email check: " . mysqli_error($conn));
        showMessageBox('An internal error occurred during registration (prepare check). Please try again.', 'register.html');
    }
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        showMessageBox('This email address is already registered.', 'register.html');
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Prepare and execute the insert statement
    $sql_query = "INSERT INTO user (name, email, password_hash, phone_number) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_query);
    if (!$stmt) {
        error_log("Prepare failed for user insert: " . mysqli_error($conn));
        showMessageBox('An internal error occurred during registration (prepare insert). Please try again.', 'register.html');
    }
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $phone);

    if (mysqli_stmt_execute($stmt)) {
        showMessageBox('Registration successful! You can now log in.', 'login.html');
    } else {
        error_log("Registration failed: " . mysqli_error($conn));
        showMessageBox('Error: Registration failed. Please try again later.', 'register.html');
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();
}


// --- 3. Login Logic ---
if (isset($_POST['login'])) {
    error_log("DEBUG: Login form submitted.");
    showMessageBox('DEBUG: Login form submitted. Email: ' . htmlspecialchars($_POST['email']), ''); // Debugging output

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql_query = "SELECT user_id, name, email, password_hash FROM user WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql_query);
    if (!$stmt) {
        error_log("Prepare failed for login query: " . mysqli_error($conn));
        showMessageBox('An internal error occurred during login (prepare query). Please try again.', 'login.html');
    }
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        error_log("DEBUG: User found in DB for email: " . $email);
        showMessageBox('DEBUG: User found. Verifying password...', ''); // Debugging output

        if (password_verify($password, $user['password_hash'])) {
            // --- SUCCESS ---
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            error_log("DEBUG: Login successful for user: " . $user['name']);
            header("Location: index.html"); // Redirect to index.html as it's now static
            exit();
        } else {
            // Password mismatch
            error_log("Login failed for email: $email - Invalid password.");
            showMessageBox('Invalid email or password!', 'login.html');
        }
    } else {
        // User not found
        error_log("Login failed: User not found for email: $email.");
        showMessageBox('Invalid email or password!', 'login.html');
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();
}

?>
