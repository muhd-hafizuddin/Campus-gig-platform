<?php
// Start the session and include the database connection.
// This allows us to know if the user is logged in.
require_once '../customerdb.php'; // Changed path to correctly include customerdb.php

// You can add PHP logic here later to dynamically load jobs from the database.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - JomBantu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* All your existing CSS from browse.html goes here */
        .job-search { background-color: #f8f9fa; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .search-form { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; }
        .search-form input, .search-form select, .search-form button { padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; }
        .search-form button { background-color: #007bff; color: white; cursor: pointer; border: none; }
        .job-listings { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .job-card { background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .job-card h3 { margin-top: 0; color: #0056b3; }
        .view-job-btn { display: block; text-align: center; background-color: #007bff; color: white; padding: 0.5rem; border-radius: 4px; text-decoration: none; margin-top: 1rem; }
    </style>
</head>
<body>

    <?php include '../header.php'; // This line replaces your entire old <header> section ?>

    <main class="container">
        <h1>Browse Campus Jobs</h1>
        
        <div class="job-search">
            <!-- Your search form remains the same -->
        </div>
        
        <div class="job-listings" id="jobListingsContainer">
            <?php
            // Fetch jobs from the database
            $jobsSql = "SELECT job_id, title, description FROM job ORDER BY created_at DESC";
            $jobsResult = mysqli_query($conn, $jobsSql);

            if (mysqli_num_rows($jobsResult) > 0) {
                while($job = mysqli_fetch_assoc($jobsResult)) {
                    echo '<div class="job-card">';
                    echo '<h3>' . htmlspecialchars($job['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars(substr($job['description'], 0, 100)) . '...</p>'; // Show snippet
                    echo '<a href="details.php?id=' . htmlspecialchars($job['job_id']) . '" class="view-job-btn">View Details</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No jobs found. Be the first to post one!</p>';
            }
            // Removed mysqli_close($conn); from here
            ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2025 JomBantu - Campus Gig Platform. All rights reserved.</p>
    </footer>

</body>
</html>
