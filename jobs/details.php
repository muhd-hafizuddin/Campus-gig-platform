<?php
// Start the session and include the database connection.
require_once 'customerdb.php';

// You can add PHP logic here to fetch job details from the database using $_GET['id']
$job_id = $_GET['id'] ?? null; // Get job ID from URL

$job = null;
if ($job_id) {
    $jobSql = "SELECT j.*, u.name as poster_name, u.email as poster_email
               FROM job j
               JOIN user u ON j.user_id = u.user_id
               WHERE j.job_id = ?";
    $stmt = mysqli_prepare($conn, $jobSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $job = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Handle case where job is not found
if (!$job) {
    showMessageBox("Job not found or invalid ID.", "browse.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - JomBantu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* All your existing CSS from details.html goes here */
        .job-details-container { max-width: 800px; margin: 2rem auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .job-header { padding: 2rem; border-bottom: 1px solid #eee; }
        .job-header h1 { color: #0056b3; margin-bottom: 0.5rem; }
        .job-header p { color: #555; font-size: 0.9em; }
        .job-content { padding: 2rem; }
        .job-content h2 { color: #333; margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
        .job-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .job-info-item strong { display: block; color: #777; font-weight: 500; margin-bottom: 0.2rem; }
        .action-buttons { display: flex; gap: 1rem; margin-top: 2rem; }
        .btn { padding: 0.8rem 1.5rem; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-primary { background-color: #007bff; color: white; border: none; }
        .btn-secondary { background-color: #6c757d; color: white; border: none; }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-content textarea {
            width: 100%;
            min-height: 100px;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-content button {
            width: auto;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; // This line replaces your entire old <header> section ?>

    <main class="container">
        <div class="job-details-container">
            <div class="job-header">
                <h1><?php echo htmlspecialchars($job['title']); ?></h1>
                <p>Posted by: <?php echo htmlspecialchars($job['poster_name']); ?> (<?php echo htmlspecialchars($job['poster_email']); ?>)</p>
                <p>Posted on: <?php echo date('d M Y', strtotime($job['created_at'])); ?></p>
            </div>
            <div class="job-content">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>

                <h2>Details</h2>
                <div class="job-info-grid">
                    <div class="job-info-item">
                        <strong>Budget:</strong> RM <?php echo htmlspecialchars(number_format($job['budget'], 2)); ?>
                    </div>
                    <div class="job-info-item">
                        <strong>Deadline:</strong> <?php echo date('d M Y', strtotime($job['deadline'])); ?>
                    </div>
                    <div class="job-info-item">
                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                    </div>
                    <div class="job-info-item">
                        <strong>Category:</strong> <?php // Fetch category name from category_id if available
                            echo htmlspecialchars($job['category_id']); // Placeholder
                        ?>
                    </div>
                    <div class="job-info-item">
                        <strong>Required Skills:</strong> <?php
                            $skills = json_decode($job['skills'], true);
                            echo htmlspecialchars(implode(', ', $skills));
                        ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="browse.php" class="btn btn-secondary">Back to Listings</a>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <button type="button" id="applyJobButton" class="btn btn-primary">Apply for this Job</button>
                    <?php else: ?>
                        <a href="../login.html" class="btn btn-primary">Login to Apply</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2025 JomBantu - Campus Gig Platform. All rights reserved.</p>
    </footer>

    <!-- The Modal -->
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Apply for Job: <?php echo htmlspecialchars($job['title']); ?></h2>
            <form id="applicationForm" action="../jobdb.php" method="post">
                <input type="hidden" name="jobId" value="<?php echo htmlspecialchars($job['job_id']); ?>">
                <div class="form-group">
                    <label for="applicationMessage">Your Message/Cover Letter:</label>
                    <textarea id="applicationMessage" name="applicationMessage" rows="5" required placeholder="Tell the job poster why you're a good fit..."></textarea>
                </div>
                <button type="submit" name="applyJob" class="btn btn-primary">Submit Application</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const applyButton = document.getElementById('applyJobButton');
            const modal = document.getElementById('applyModal');
            const closeButton = document.querySelector('.close-button');

            if (applyButton) {
                applyButton.addEventListener('click', function() {
                    modal.style.display = 'flex'; // Use flex to center
                });
            }

            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            // Close the modal if the user clicks anywhere outside of it
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn); // Close the database connection
?>
