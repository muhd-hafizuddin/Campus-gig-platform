<?php
    $servername="localhost";
    $username="root";
    $password="";
    $dbname="customerdb";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn)
    {
        die("Connection failed:" .mysqli_connect_error());
    }

    // Register user
    if(isset($_POST['register'])) // name of submit button
    {
        $Name = $_POST['fullName'];
        $Email = $_POST['email'];
        $Password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $Phone = $_POST['phoneNum'];
        $Profile = 'images/profile.jpg'; // Default profile picture
        $isActive = 'active'; // Default status

        $sql_query = "INSERT INTO user (name, email, password_hash, phone_number, profile_picture_url, is_active, created_at, updated_at) 
        VALUES ('$Name', '$Email', '$Password', '$Phone' ,'$Profile', '$isActive', NOW(), NOW())";
        
        if(mysqli_query($conn, $sql_query))
        {
            // Registration successful, redirect to login page
            header("Location: login.html?registration=success");
            exit; // Important to exit after header redirect
        }
        else
        {
            // Registration failed, redirect back to register with an error or show error
            // For a simple app, we can redirect back or just echo an error
            echo "Error: " . $sql_query . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
    }

    // Login user
    elseif(isset($_POST['login'])) // name of login form submit button
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepare and execute statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, password_hash FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify password hash
            if (password_verify($password, $row['password_hash'])) {
                // Login successful, redirect to index page
                // In a real app, you would start a session here (e.g., $_SESSION['user_id'] = $row['user_id'];)
                header("Location: index.html?login=success");
                exit; // Important to exit after header redirect
            } else {
                // Password does not match
                echo "Invalid email or password.";
            }
        } else {
            // No user found with that email
            echo "Invalid email or password.";
        }
        $stmt->close();
        mysqli_close($conn);
    }
?>
