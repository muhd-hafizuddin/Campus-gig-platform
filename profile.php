<?php
// 1. REQUIRE THE DATABASE CONNECTION AND SESSION START
// This file should have session_start() at the top.
require_once 'customerdb.php'; // customerdb.php already calls session_start()

// 2. AUTHENTICATION CHECK
// Redirect users to the login page if they are not logged in.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

// 3. GET USER ID FROM SESSION
// We will use this ID to fetch all the relevant data.
$userId = $_SESSION['id'];

// 4. FETCH USER'S PROFILE DATA
// Prepare a statement to prevent SQL injection.
$userSql = "SELECT name, email, phone_number, profile_picture_url, created_at FROM user WHERE user_id = ?";
$userStmt = mysqli_prepare($conn, $userSql);
mysqli_stmt_bind_param($userStmt, "i", $userId);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$user = mysqli_fetch_assoc($userResult);

// Handle case where user is not found (should be rare if session is valid)
if (!$user) {
    showMessageBox("Could not find user profile.", "index.php");
}

// 5. FETCH USER'S RATING AND REVIEW COUNT
$ratingSql = "SELECT AVG(rating) as avg_rating, COUNT(review_id) as review_count FROM review WHERE reviewee_id = ?";
$ratingStmt = mysqli_prepare($conn, $ratingSql);
mysqli_stmt_bind_param($ratingStmt, "i", $userId);
mysqli_stmt_execute($ratingStmt);
$ratingResult = mysqli_stmt_get_result($ratingStmt);
$ratingData = mysqli_fetch_assoc($ratingResult);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['review_count'];

// 6. FETCH ALL REVIEWS FOR THE USER
$reviewsSql = "SELECT r.rating, r.comment, r.created_at, u.name as reviewer_name 
               FROM review r 
               JOIN user u ON r.reviewer_id = u.user_id 
               WHERE r.reviewee_id = ? 
               ORDER BY r.created_at DESC";
$reviewsStmt = mysqli_prepare($conn, $reviewsSql);
mysqli_stmt_bind_param($reviewsStmt, "i", $userId);
mysqli_stmt_execute($reviewsStmt);
$reviewsResult = mysqli_stmt_get_result($reviewsStmt);

$posterRating = getUserAverageRating($conn, $userId, 'worker_to_poster');
$takerRating = getUserAverageRating($conn, $userId, 'poster_to_worker');
$posterReviewCount = getUserReviewCount($conn, $userId, 'worker_to_poster');
$takerReviewCount = getUserReviewCount($conn, $userId, 'poster_to_worker');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - JomBantu</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Add specific styles for the profile page */
        .profile-container { max-width: 900px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .profile-header { display: flex; align-items: center; gap: 2rem; border-bottom: 1px solid #eee; padding-bottom: 2rem; margin-bottom: 2rem; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; background-color: #ddd; }
        .profile-header-info h1 { margin: 0; color: var(--primary-blue); }
        .profile-header-info p { margin: 0.5rem 0 0; color: #555; }
        .profile-stats { display: flex; gap: 1.5rem; margin-top: 1rem; }
        .stat-item { font-size: 1.1rem; }
        .stat-item .star { color: #f39c12; }
        .profile-content { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        .profile-details ul { list-style: none; padding: 0; }
        .profile-details li { margin-bottom: 1rem; color: #333; }
        .profile-details li strong { display: block; color: #777; font-weight: 500; margin-bottom: 0.2rem; }
        
        .profile-main-content section { margin-bottom: 3rem; }
        .profile-main-content h2 { color: var(--primary-blue); border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        
        .jobs-grid, .applications-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .job-card, .application-card { background-color: white; border: 1px solid #eee; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .job-card h3, .application-card h3 { margin-top: 0; color: #0056b3; margin-bottom: 0.5rem; }
        .job-category, .app-poster { color: #666; font-size: 0.9em; margin-bottom: 0.5rem; }
        .job-budget, .app-budget { color: #28a745; font-weight: 600; margin-bottom: 0.5rem; }
        .job-status, .app-status { margin-bottom: 0.5rem; }
        .status-open { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-accepted { color: #28a745; }
        .status-rejected { color: #dc3545; }
        .status-completed { color: #6c757d; }
        .job-applications, .app-date { color: #777; font-size: 0.9em; margin-bottom: 1rem; }
        .view-job-btn { display: block; text-align: center; background-color: #007bff; color: white; padding: 0.8rem; border-radius: 4px; text-decoration: none; transition: background-color 0.2s ease; }
        .view-job-btn:hover { background-color: #0056b3; }
        
        .reviews-section h2 { color: var(--primary-blue); border-bottom: 1px solid #eee; padding-bottom: 1rem; }
        .review-card { border: 1px solid #eee; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .review-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .review-card-header h3 { margin: 0; font-size: 1.1rem; }
        .review-rating { color: #f39c12; font-weight: bold; }
        .review-comment { color: #444; line-height: 1.6; }
        .review-date { font-size: 0.85rem; color: #999; text-align: right; margin-top: 1rem; }
        .no-reviews { text-align: center; padding: 2rem; color: #777; }
    </style>
</head>
<body>
    
    <!-- You should include your site's header here -->
    <?php include 'header.php'; // I recommend creating a reusable header.php file ?>

    <main class="container">
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($user['profile_picture_url'] ?? 'images/default-avatar.png'); ?>" alt="Profile Avatar" class="profile-avatar">
                <div class="profile-header-info">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <div class="profile-stats">
                        <span class="stat-item"><span class="star">★</span> <?php echo $posterRating; ?> average rating as poster (<?php echo $posterReviewCount; ?> reviews)</span>
                        <?php if ($posterReviewCount == 0) echo '<span style="color:#888;">This user has not received any reviews as a poster yet.</span>'; ?>
                        <br>
                        <span class="stat-item"><span class="star">★</span> <?php echo $takerRating; ?> average rating as job taker (<?php echo $takerReviewCount; ?> reviews)</span>
                        <?php if ($takerReviewCount == 0) echo '<span style="color:#888;">This user has not received any reviews as a job taker yet.</span>'; ?>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <aside class="profile-details">
                    <h2>Contact Information</h2>
                    <ul>
                        <li>
                            <strong>Email</strong>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </li>
                        <li>
                            <strong>Phone Number</strong>
                            <?php echo htmlspecialchars($user['phone_number'] ?? 'Not provided'); ?>
                        </li>
                    </ul>
                    <div style="margin-top: 2rem;">
                        <a href="jobs/create.php" class="btn btn-primary" style="display: block; text-align: center; padding: 0.8rem; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Post New Job</a>
                    </div>
                </aside>

                <div class="profile-main-content">
                    <!-- My Posted Jobs Section -->
                    <section class="jobs-section">
                        <h2>My Posted Jobs</h2>
                        <?php
                        $userJobs = getUserJobs($conn, $userId);
                        if (!empty($userJobs)):
                        ?>
                            <div class="jobs-grid">
                                <?php foreach($userJobs as $job): ?>
                                    <div class="job-card">
                                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                        <p class="job-category"><?php echo htmlspecialchars($job['category_name']); ?></p>
                                        <p class="job-budget">Budget: RM <?php echo htmlspecialchars($job['budget']); ?></p>
                                        <p class="job-status">Status: <span class="status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></p>
                                        <p class="job-applications"><?php echo $job['application_count']; ?> applications</p>
                                        <a href="jobs/details.php?id=<?php echo $job['job_id']; ?>" class="view-job-btn">View Details</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>You haven't posted any jobs yet. <a href="jobs/create.php">Post your first job!</a></p>
                        <?php endif; ?>
                    </section>

                    <!-- My Applications Section -->
                    <section class="applications-section">
                        <h2>My Applications</h2>
                        <?php
                        $userApplications = getUserApplications($conn, $userId);
                        if (!empty($userApplications)):
                        ?>
                            <div class="applications-grid">
                                <?php foreach($userApplications as $app): ?>
                                    <div class="application-card">
                                        <h3><?php echo htmlspecialchars($app['job_title']); ?></h3>
                                        <p class="app-poster">Posted by: <?php echo htmlspecialchars($app['poster_name']); ?></p>
                                        <p class="app-budget">Budget: RM <?php echo htmlspecialchars($app['budget']); ?></p>
                                        <p class="app-status">Status: <span class="status-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></p>
                                        <p class="app-date">Applied: <?php echo date('M j, Y', strtotime($app['applied_at'])); ?></p>
                                        <a href="jobs/details.php?id=<?php echo $app['job_id']; ?>" class="view-job-btn">View Job</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>You haven't applied to any jobs yet. <a href="jobs/browse.php">Browse available jobs!</a></p>
                        <?php endif; ?>
                    </section>

                    <!-- Reviews Section -->
                    <section class="reviews-section">
                        <h2>Reviews</h2>
                        <?php if ($reviewCount > 0): ?>
                            <?php while ($review = mysqli_fetch_assoc($reviewsResult)): ?>
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <h3><?php echo htmlspecialchars($review['reviewer_name']); ?></h3>
                                        <div class="review-rating">
                                            <?php echo str_repeat('★', round($review['rating'])); ?><?php echo str_repeat('☆', 5 - round($review['rating'])); ?>
                                            (<?php echo htmlspecialchars($review['rating']); ?>)
                                        </div>
                                    </div>
                                    <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <p class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-reviews">
                                <p>This user has not received any reviews yet.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- You should include your site's footer here -->
    <?php // include 'footer.php'; ?>

    <?php if (isset($_SESSION['show_notification_modal']) && !empty($_SESSION['show_notification_modal'])): ?>
    <div id="notifModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;">
        <div style="background:#fff;padding:32px 40px;border-radius:10px;max-width:400px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.15);">
            <h2>Notifications</h2>
            <ul style="list-style:none;padding:0;">
            <?php foreach ($_SESSION['show_notification_modal'] as $notif): ?>
                <li style="margin-bottom:18px;">
                    <?php
                    // Try to extract job id from message if possible
                    $jobId = null;
                    if (preg_match('/job: ([^\(]+)\(ID: (\d+)\)/', $notif['message'], $m)) {
                        $jobTitle = $m[1];
                        $jobId = $m[2];
                        echo htmlspecialchars($notif['message']) . '<br>';
                        echo '<a href="jobs/details.php?id=' . intval($jobId) . '">Go to Job Details</a>';
                    } elseif (preg_match('/job: (.+)$/', $notif['message'], $m)) {
                        // fallback: just show jobs list
                        echo htmlspecialchars($notif['message']) . '<br>';
                        echo '<a href="jobs/browse.php">Go to Jobs</a>';
                    } else {
                        echo htmlspecialchars($notif['message']);
                    }
                    ?>
                </li>
            <?php endforeach; ?>
            </ul>
            <button onclick="document.getElementById('notifModal').style.display='none';">Close</button>
        </div>
    </div>
    <script>window.onload = function(){document.getElementById('notifModal').style.display='flex';}</script>
    <?php unset($_SESSION['show_notification_modal']); endif; ?>

</body>
</html>
<?php
// 7. CLEAN UP
mysqli_stmt_close($userStmt);
mysqli_stmt_close($ratingStmt);
mysqli_stmt_close($reviewsStmt);
mysqli_close($conn);
?>
