<?php
    $servername="localhost";
    $username="root";
    $password="";
    $dbname="customerdb"; // Assuming 'customerdb' is the database where job table resides

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn)
    {
        die("Connection failed:" .mysqli_connect_error());
    }

    // Handle job posting
    if(isset($_POST['postJob'])) // Name of the submit button in create.html
    {
        // Sanitize and retrieve form data
        $title = mysqli_real_escape_string($conn, $_POST['jobTitle']);
        $category_id = mysqli_real_escape_string($conn, $_POST['jobCategory']); // Assuming category_id is passed directly
        $description = mysqli_real_escape_string($conn, $_POST['jobDescription']);
        $budget = mysqli_real_escape_string($conn, $_POST['jobBudget']);
        $deadline = mysqli_real_escape_string($conn, $_POST['jobDeadline']);
        $location = mysqli_real_escape_string($conn, $_POST['jobLocation']);
        $user_id = 1; // Placeholder: In a real app, this would come from the logged-in user's session
        $status = 'pending'; // Default status for new jobs

        // Handle skills (if any)
        $skills_array = isset($_POST['skills']) ? $_POST['skills'] : [];
        $skills_json = json_encode($skills_array); // Store skills as JSON string

        // SQL query to insert job data
        $sql_query = "INSERT INTO job (user_id, category_id, title, description, budget, status, deadline, location, skills, created_at, updated_at) 
                      VALUES ('$user_id', '$category_id', '$title', '$description', '$budget', '$status', '$deadline', '$location', '$skills_json', NOW(), NOW())";

        if(mysqli_query($conn, $sql_query))
        {
            // Redirect to a success page or browse jobs page
            header("Location: browse.html?status=success");
            exit;
        }
        else
        {
            echo "Error: " . $sql_query . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
    }

    // Handle job application submission (if extended for applications)
    if(isset($_POST['applyJob'])) {
        $job_id = mysqli_real_escape_string($conn, $_POST['jobId']);
        $user_id = 1; // Placeholder: In a real app, this would come from the logged-in user's session
        $message = mysqli_real_escape_string($conn, $_POST['applicationMessage']);
        $status = 'applied'; // Default status for new applications

        $sql_query = "INSERT INTO application (job_id, user_id, message, status, applied_at, updated_at)
                      VALUES ('$job_id', '$user_id', '$message', '$status', NOW(), NOW())";

        if(mysqli_query($conn, $sql_query)) {
            // Success response (can be a simple echo or JSON for AJAX)
            echo "Application submitted successfully!";
        } else {
            echo "Error: " . $sql_query . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
?>
