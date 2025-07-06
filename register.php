<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JomBantu</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <a href="index.php" class="logo">
                <img src="images/logo.png" alt="JomBantu Logo">
                <span>JomBantu</span>
            </a>
            <h1>Create Your Account</h1>
            <p>Join our campus gig platform today</p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message" style="background:#ffe0e0;color:#b30000;padding:12px 18px;border-radius:6px;margin-bottom:18px;">
                <?php
                $err = $_GET['error'];
                if ($err === 'password') echo 'Passwords do not match!';
                elseif ($err === 'email') echo 'Invalid email format. Email must be in the format name@student.uitm.edu.my';
                elseif ($err === 'exists') echo 'This email address is already registered.';
                elseif ($err === 'internal') echo 'An internal error occurred. Please try again.';
                elseif ($err === 'fail') echo 'Registration failed. Please try again later.';
                else echo 'An unknown error occurred.';
                ?>
            </div>
        <?php endif; ?>
        
        <form id="registerForm" name="register" action="customerdb.php" method="post" class="auth-form">
            <div class="form-group">
                <label for="fullName">Full Name*</label>
                <input type="text" id="fullName" name="fullName" required value="<?php if(isset($_GET['name'])) echo htmlspecialchars($_GET['name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" required value="<?php if(isset($_GET['email'])) echo htmlspecialchars($_GET['email']); ?>">
            </div>
            <div class="form-group">
                <label for="phoneNum">Phone Number</label>
                <input type="text" id="phoneNum" name="phoneNum" value="<?php if(isset($_GET['phone'])) echo htmlspecialchars($_GET['phone']); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password*</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                <small class="password-hint">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirm Password*</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="••••••••">
            </div>
            
            <div class="form-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>
            
            <button type="submit" name="register" class="auth-button">Register</button>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.html">Login here</a></p>
            </div>
        </form>
    </div>

   <script src="js/auth.js"></script>
</body>
</html>