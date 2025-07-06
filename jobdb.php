<?php
// Always start the session at the very top to access session variables.
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "customerdb"; // Assuming 'customerdb' is the database where job table resides

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed:" . mysqli_connect_error());
}

// Function to display a custom message box (replaces alert())
// This function is duplicated from customerdb.php for standalone use in jobdb.php
// In a larger application, this would ideally be in a shared utility file.
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


// Handle job posting
if (isset($_POST['postJob'])) {
    // Check if the user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        showMessageBox("You must be logged in to post a job.", "../login.html");
    }

    // Sanitize and retrieve form data
    $title = mysqli_real_escape_string($conn, $_POST['jobTitle']);
    $category_id = mysqli_real_escape_string($conn, $_POST['jobCategory']); // Assuming category_id is passed directly
    $description = mysqli_real_escape_string($conn, $_POST['jobDescription']);
    $budget = mysqli_real_escape_string($conn, $_POST['jobBudget']);
    $deadline = mysqli_real_escape_string($conn, $_POST['jobDeadline']);
    $location = mysqli_real_escape_string($conn, $_POST['jobLocation']);
    
    // Get user_id from session
    $user_id = $_SESSION['id']; 
    $status = 'pending'; // Default status for new jobs

    // Handle skills (if any)
    $skills_array = isset($_POST['skills']) ? $_POST['skills'] : [];
    $skills_json = json_encode($skills_array); // Store skills as JSON string

    // SQL query to insert job data
    $sql_query = "INSERT INTO job (user_id, category_id, title, description, budget, status, deadline, location, skills, created_at, updated_at) 
                  VALUES ('$user_id', '$category_id', '$title', '$description', '$budget', '$status', '$deadline', '$location', '$skills_json', NOW(), NOW())";

    if (mysqli_query($conn, $sql_query)) {
        // Redirect to a success page or browse jobs page
        header("Location: browse.php?status=success"); // Changed to browse.php
        exit;
    } else {
        error_log("Error posting job: " . $sql_query . " - " . mysqli_error($conn));
        showMessageBox('Error: Could not post job. Please try again.', 'create.php');
    }
    mysqli_close($conn);
}

// Handle job application submission (if extended for applications)
if (isset($_POST['applyJob'])) {
    // Check if the user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        showMessageBox("You must be logged in to apply for a job.", "../login.html");
    }

    $job_id = mysqli_real_escape_string($conn, $_POST['jobId']);
    // Get user_id from session
    $user_id = $_SESSION['id']; 
    $message = mysqli_real_escape_string($conn, $_POST['applicationMessage']);
    $status = 'applied'; // Default status for new applications

    $sql_query = "INSERT INTO application (job_id, user_id, message, status, applied_at, updated_at)
                  VALUES ('$job_id', '$user_id', '$message', '$status', NOW(), NOW())";

    if (mysqli_query($conn, $sql_query)) {
        showMessageBox("Application submitted successfully!", "details.php?id=" . $job_id);
    } else {
        error_log("Error applying for job: " . $sql_query . " - " . mysqli_error($conn));
        showMessageBox("Error: Could not submit application. Please try again.", "details.php?id=" . $job_id);
    }
    mysqli_close($conn);
}
?>
