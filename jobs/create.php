<?php
// 1. REQUIRE THE DATABASE CONNECTION AND SESSION START
require_once '../customerdb.php';

// 2. AUTHENTICATION CHECK
// If 'loggedin' is not set in the session or is not true, redirect to login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Optionally, you can show a message before redirecting.
    showMessageBox("You must be logged in to post a job.", "login.html");
    // header('Location: login.html'); // showMessageBox handles exit
    exit;
}

// The rest of your existing HTML/form code goes here.
// The form action should point to a PHP script that handles job submission.
// For example: <form action="handlers/post_job.php" method="post">
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job - JomBantu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Your existing CSS from create.html goes here */
        .job-form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .job-form h2 { color: var(--primary-blue); margin-top: 0; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark-grey); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
        .btn { padding: 0.8rem 1.5rem; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .btn-primary { background-color: var(--secondary-blue); color: white; border: none; }
        .btn-secondary { background-color: white; color: var(--secondary-blue); border: 1px solid var(--secondary-blue); }
    </style>
</head>
<body>
    <!-- It's best practice to use a reusable header file -->
    <?php 
    $is_subdirectory = true;
    include '../header.php'; 
    ?>

    <main class="container">
        <div class="job-form-container">
            <!-- Make sure your form action points to the correct handler script -->
            <form class="job-form" id="jobCreationForm" action="../customerdb.php" method="post">
                <h2>Create New Job Posting</h2>
                
                <!-- All your form groups from create.html go here -->
                <div class="form-group">
                    <label for="jobTitle">Job Title*</label>
                    <input type="text" id="jobTitle" name="jobTitle" required placeholder="e.g. Graphic Designer for Event Poster">
                </div>
                
                <div class="form-group">
                    <label for="jobDescription">Job Description*</label>
                    <textarea id="jobDescription" name="jobDescription" required placeholder="Describe the job in detail..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jobCategory">Category*</label>
                        <select id="jobCategory" name="jobCategory" required>
                            <option value="">Select a category</option>
                            <?php
                            $categories = getCategories($conn);
                            foreach($categories as $category) {
                                echo '<option value="' . htmlspecialchars($category['category_id']) . '">' . htmlspecialchars($category['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jobBudget">Budget (RM)*</label>
                        <input type="number" id="jobBudget" name="jobBudget" min="0" step="5" required placeholder="50">
                    </div>
                    <div class="form-group">
                        <label for="jobDeadline">Deadline*</label>
                        <input type="date" id="jobDeadline" name="jobDeadline" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='browse.php'">Cancel</button>
                    <button type="submit" name="postJob" class="btn btn-primary">Post Job</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Your footer can also be an include -->
    <?php // include 'footer.php'; ?>
</body>
</html>
