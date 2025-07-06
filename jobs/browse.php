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
        .job-card { background-color: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s ease; }
        .job-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .job-card h3 { margin-top: 0; color: #0056b3; margin-bottom: 0.5rem; }
        .job-category { color: #666; font-size: 0.9em; margin-bottom: 0.5rem; }
        .job-budget { color: #28a745; font-weight: 600; margin-bottom: 0.5rem; }
        .job-description { color: #555; line-height: 1.5; margin-bottom: 1rem; }
        .job-poster { color: #777; font-size: 0.9em; margin-bottom: 0.3rem; }
        .job-date { color: #999; font-size: 0.8em; margin-bottom: 1rem; }
        .view-job-btn { display: block; text-align: center; background-color: #007bff; color: white; padding: 0.8rem; border-radius: 4px; text-decoration: none; margin-top: 1rem; transition: background-color 0.2s ease; }
        .view-job-btn:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; color: white; padding: 0.8rem 1rem; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; }
        .btn-secondary:hover { background-color: #545b62; }
    </style>
</head>
<body>

    <?php 
    $is_subdirectory = true;
    include '../header.php'; // This line replaces your entire old <header> section 
    ?>

    <main class="container">
        <h1>Browse Campus Jobs</h1>
        
        <div class="job-search">
            <form class="search-form" method="GET" action="">
                <input type="text" name="search" placeholder="Search jobs..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = getCategories($conn);
                    foreach($categories as $category) {
                        $selected = ($_GET['category'] ?? '') == $category['category_id'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($category['category_id']) . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit">Search</button>
                <a href="browse.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>
        
        <div class="job-listings" id="jobListingsContainer">
            <?php
            // Get search parameters
            $search = $_GET['search'] ?? null;
            $category_id = $_GET['category'] ?? null;
            
            // Fetch jobs using the new function
            $jobs = getJobs($conn, 50, $category_id, $search);

            if (!empty($jobs)) {
                foreach($jobs as $job) {
                    echo '<div class="job-card">';
                    // Profile picture
                    $pic = $job['profile_picture_url'] ? '../' . htmlspecialchars($job['profile_picture_url']) : '../images/default-avatar.png';
                    echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">';
                    echo '<img src="' . $pic . '" alt="Profile" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">';
                    echo '<div><strong>' . htmlspecialchars($job['poster_name']) . '</strong><br>';
                    // Poster rating
                    $stars = str_repeat('★', floor($job['poster_rating'])) . str_repeat('☆', 5-floor($job['poster_rating']));
                    echo '<span style="color:#f39c12;">' . $stars . '</span> <span style="font-size:0.95em;">(' . $job['poster_rating'] . ')</span></div>';
                    echo '</div>';
                    echo '<h3>' . htmlspecialchars($job['title']) . '</h3>';
                    echo '<p class="job-category">' . htmlspecialchars($job['category_name']) . '</p>';
                    echo '<p class="job-budget">Budget: RM ' . htmlspecialchars($job['budget']) . '</p>';
                    echo '<p class="job-description">' . htmlspecialchars(substr($job['description'], 0, 150)) . '...</p>';
                    echo '<p class="job-date">Posted: ' . date('M j, Y', strtotime($job['created_at'])) . '</p>';
                    echo '<a href="details.php?id=' . htmlspecialchars($job['job_id']) . '" class="view-job-btn">View Details</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No jobs found. Be the first to post one!</p>';
            }
            ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2025 JomBantu - Campus Gig Platform. All rights reserved.</p>
    </footer>

</body>
</html>
