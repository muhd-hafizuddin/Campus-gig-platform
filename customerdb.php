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
    //register user
    if(isset($_POST['register']))//name of submit button
    {
        $Name = $_POST['fullName'];
        $Email = $_POST['email'];
        $Password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $Phone = $_POST['phoneNum'];
        $Profile = 'images/profile.jpg';
        $isActive = 'active';

        $sql_query = "INSERT INTO user (name, email, password_hash, phone_number, profile_picture_url, is_active, created_at, updated_at) 
        VALUES ('$Name', '$Email', '$Password', '$Phone' ,'$Profile', '$isActive', NOW(), NOW())";
        //running query
        if(mysqli_query($conn, $sql_query))
        {
            echo "Data inserted successfully";
        }
        else
        {
            echo "Error:" . $sql_query."". mysqli_error($conn);
        }
        mysqli_close($conn);
    }
    //login user
    elseif(isset($_POST['login']))//name of login form
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql_query = "SELECT * FROM user";
        $result = mysqli_query($conn, $sql_query);
        $loginSuccess = false;

        while($row = mysqli_fetch_assoc($result)){
            if ($row['email'] === $email && $row['password'] === $password) {
                $loginSuccess = true;
                break;
            }
        }
        if ($loginSuccess) {
            echo "Login Successful Cuk";
            header("Location: index.html");
            exit;
        }
        else {
            echo "tak berjaya la cuba lagi plis";
        }
    }
    ?>
    