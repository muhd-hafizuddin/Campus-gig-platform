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
    showMessageBox('A database connection error occurred. Please try again later.', 'index.php');
}

// --- 2. Registration Logic ---
if (isset($_POST['register'])) {

    $name = $_POST['fullName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPass = $_POST['confirmPassword'];
    $phone = $_POST['phoneNum'];

    if ($password !== $confirmPass) {
        header('Location: register.php?name=' . urlencode($name) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone) . '&error=password');
        exit();
    }

    // Validate email format
    // This regex checks for 'anything'@student.uitm.edu.my
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@student\.uitm\.edu\.my$/', $email)) {
        header('Location: register.php?name=' . urlencode($name) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone) . '&error=email');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $check_sql = "SELECT email FROM user WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if (!$check_stmt) {
        error_log("Prepare failed for email check: " . mysqli_error($conn));
        showMessageBox('An internal error occurred during registration (prepare check). Please try again.', 'register.php');
    }
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        header('Location: register.php?name=' . urlencode($name) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone) . '&error=exists');
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Prepare and execute the insert statement
    $sql_query = "INSERT INTO user (name, email, password_hash, phone_number) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_query);
    if (!$stmt) {
        header('Location: register.php?name=' . urlencode($name) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone) . '&error=internal');
        exit();
    }
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $phone);

    if (mysqli_stmt_execute($stmt)) {
        showMessageBox('Registration successful! You can now log in.', 'login.html');
    } else {
        header('Location: register.php?name=' . urlencode($name) . '&email=' . urlencode($email) . '&phone=' . urlencode($phone) . '&error=fail');
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();
}


// --- 3. Login Logic ---
if (isset($_POST['login'])) {
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
        if (password_verify($password, $user['password_hash'])) {
            // --- SUCCESS ---
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            // Check for unread notifications
            $notif_sql = "SELECT * FROM notification WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
            $notif_stmt = mysqli_prepare($conn, $notif_sql);
            if ($notif_stmt) {
                mysqli_stmt_bind_param($notif_stmt, "i", $user['user_id']);
                mysqli_stmt_execute($notif_stmt);
                $notif_result = mysqli_stmt_get_result($notif_stmt);
                $notifs = [];
                while ($row = mysqli_fetch_assoc($notif_result)) {
                    $notifs[] = $row;
                }
                mysqli_stmt_close($notif_stmt);
                if (!empty($notifs)) {
                    $_SESSION['show_notification_modal'] = $notifs;
                }
            }
            header("Location: profile.php");
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

// Removed the conditional mysqli_close($conn); from here
// The connection will now remain open for scripts that include customerdb.php
// and handle their own closing or rely on script termination.

// --- 4. Job Management Functions ---

// Function to get all categories
function getCategories($conn) {
    $sql = "SELECT category_id, name, description FROM category ORDER BY name";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Helper: Get average rating for a user as poster or job taker
function getUserAverageRating($conn, $user_id, $type = 'worker_to_poster') {
    $sql = "SELECT AVG(rating) as avg_rating FROM review WHERE reviewee_id = ? AND review_type = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return 0;
    mysqli_stmt_bind_param($stmt, "is", $user_id, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row && $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
}

// Function to get all jobs with poster's average rating
function getJobs($conn, $limit = 50, $category_id = null, $search = null) {
    $sql = "SELECT j.job_id, j.title, j.description, j.budget, j.status, j.deadline, j.created_at,
                   u.user_id as poster_id, u.name as poster_name, u.profile_picture_url, c.name as category_name
            FROM job j 
            JOIN user u ON j.user_id = u.user_id 
            JOIN category c ON j.category_id = c.category_id 
            WHERE j.status = 'open'";
    $params = [];
    $types = "";
    if ($category_id) {
        $sql .= " AND j.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    if ($search) {
        $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
    $sql .= " ORDER BY j.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed for jobs query: " . mysqli_error($conn));
        return [];
    }
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['poster_rating'] = getUserAverageRating($conn, $row['poster_id'], 'worker_to_poster');
        $jobs[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $jobs;
}

// Function to get a single job by ID
function getJobById($conn, $job_id) {
    $job_id = mysqli_real_escape_string($conn, $job_id);
    
    $sql = "SELECT j.job_id, j.user_id, j.category_id, j.title, j.description, j.budget, j.status, j.deadline, j.created_at, j.updated_at, u.name as poster_name, u.email as poster_email, c.name as category_name
            FROM job j 
            JOIN user u ON j.user_id = u.user_id 
            JOIN category c ON j.category_id = c.category_id 
            WHERE j.job_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed for job query: " . mysqli_error($conn));
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $job;
}

// Function to apply for a job (no message field)
function applyForJob($conn, $job_id, $user_id) {
    // Check if user already applied
    $check_sql = "SELECT application_id FROM application WHERE job_id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if (!$check_stmt) {
        return false;
    }
    mysqli_stmt_bind_param($check_stmt, "ii", $job_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        mysqli_stmt_close($check_stmt);
        return false; // Already applied
    }
    mysqli_stmt_close($check_stmt);
    // Insert application (no message)
    $sql = "INSERT INTO application (job_id, user_id, status, applied_at, updated_at) 
            VALUES (?, ?, 'pending', NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Function to get user's posted jobs
function getUserJobs($conn, $user_id) {
    $sql = "SELECT j.*, c.name as category_name, 
                   (SELECT COUNT(*) FROM application a WHERE a.job_id = j.job_id) as application_count
            FROM job j 
            JOIN category c ON j.category_id = c.category_id 
            WHERE j.user_id = ? 
            ORDER BY j.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $jobs;
}

// Function to get user's applications
function getUserApplications($conn, $user_id) {
    $sql = "SELECT a.*, j.title as job_title, j.budget, u.name as poster_name
            FROM application a 
            JOIN job j ON a.job_id = j.job_id 
            JOIN user u ON j.user_id = u.user_id 
            WHERE a.user_id = ? 
            ORDER BY a.applied_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $applications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $applications[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $applications;
}

// Function to post a new job (always status 'open')
function postJob($conn, $user_id, $title, $description, $budget, $deadline, $category_id = 1) {
    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);
    $budget = mysqli_real_escape_string($conn, $budget);
    $deadline = mysqli_real_escape_string($conn, $deadline);
    $category_id = mysqli_real_escape_string($conn, $category_id);
    $sql = "INSERT INTO job (user_id, category_id, title, description, budget, status, deadline, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'open', ?, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed for job insert: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "iissds", $user_id, $category_id, $title, $description, $budget, $deadline);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Soft delete job (set status)
function softDeleteJob($conn, $job_id, $status = 'end') {
    $sql = "UPDATE job SET status = ?, updated_at = NOW() WHERE job_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "si", $status, $job_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Add notification for user
function addNotification($conn, $user_id, $message) {
    $sql = "INSERT INTO notification (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Handle job posting from form
if (isset($_POST['postJob'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        showMessageBox("You must be logged in to post a job.", "login.html");
    }

    $title = $_POST['jobTitle'] ?? '';
    $description = $_POST['jobDescription'] ?? '';
    $budget = $_POST['jobBudget'] ?? 0;
    $deadline = $_POST['jobDeadline'] ?? '';
    $category_id = $_POST['jobCategory'] ?? 1;
    
    if (empty($title) || empty($description) || empty($budget) || empty($deadline)) {
        showMessageBox("Please fill in all required fields.", "jobs/create.php");
    }
    
    $user_id = $_SESSION['id'];
    
    if (postJob($conn, $user_id, $title, $description, $budget, $deadline, $category_id)) {
        showMessageBox("Job posted successfully!", "jobs/browse.php");
    } else {
        showMessageBox("Error posting job. Please try again.", "jobs/create.php");
    }
}

// Handle job application from form (add notification)
if (isset($_POST['applyJob'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        showMessageBox("You must be logged in to apply for a job.", "login.html");
    }
    $job_id = $_POST['jobId'] ?? 0;
    $user_id = $_SESSION['id'];
    if (empty($job_id)) {
        showMessageBox("Invalid job ID.", "jobs/browse.php");
    }
    if (applyForJob($conn, $job_id, $user_id)) {
        // Notify the poster
        $job = getJobById($conn, $job_id);
        if ($job) {
            addNotification($conn, $job['user_id'], "You have a new applicant for your job: " . $job['title']);
        }
        showMessageBox("Application submitted successfully!", "jobs/details.php?id=" . $job_id);
    } else {
        showMessageBox("Error submitting application or you have already applied.", "jobs/details.php?id=" . $job_id);
    }
}

// Profile picture upload handler
if (isset($_POST['uploadProfilePic']) && isset($_FILES['profilePic'])) {
    $user_id = $_SESSION['id'];
    $file = $_FILES['profilePic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $target = 'images/profile_' . $user_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $sql = "UPDATE user SET profile_picture_url = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $target, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    header('Location: profile.php');
    exit();
}

// Handle accept/reject applicant (from job details page)
if ((isset($_POST['accept_applicant']) || isset($_POST['reject_applicant'])) && isset($_POST['job_id']) && isset($_POST['applicant_id'])) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        showMessageBox("You must be logged in as the job poster to perform this action.", "jobs/browse.php");
    }
    $job_id = intval($_POST['job_id']);
    $applicant_id = intval($_POST['applicant_id']);
    $poster_id = $_SESSION['id'];
    // Check if the logged-in user is the poster of this job
    $job = getJobById($conn, $job_id);
    if (!$job || $job['user_id'] != $poster_id) {
        showMessageBox("You are not authorized to perform this action.", "jobs/details.php?id=$job_id");
    }
    $new_status = isset($_POST['accept_applicant']) ? 'accepted' : 'rejected';
    $sql = "UPDATE application SET status = ?, updated_at = NOW() WHERE job_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $job_id, $applicant_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    // Optionally, notify the applicant
    $msg = ($new_status == 'accepted') ? "Your application for job '{$job['title']}' was accepted!" : "Your application for job '{$job['title']}' was rejected.";
    addNotification($conn, $applicant_id, $msg);
    header("Location: jobs/details.php?id=$job_id");
    exit();
}

function getUserReviewCount($conn, $user_id, $type = 'worker_to_poster') {
    $sql = "SELECT COUNT(*) as review_count FROM review WHERE reviewee_id = ? AND review_type = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return 0;
    mysqli_stmt_bind_param($stmt, "is", $user_id, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row && $row['review_count'] ? intval($row['review_count']) : 0;
}

?>
