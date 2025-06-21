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
    if(isset($_POST['save']))//name of submit button
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

    