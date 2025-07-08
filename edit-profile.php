<?php
require_once 'customerdb.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['id'];
$feedback = '';
$view = $_GET['view'] ?? '';

// Handle profile picture upload
if (isset($_POST['update_picture'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = basename($_FILES['profile_picture']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
            $destPath = 'images/' . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Update DB
                $stmt = mysqli_prepare($conn, "UPDATE user SET profile_picture_url = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $destPath, $userId);
                mysqli_stmt_execute($stmt);
                $feedback = '<span style="color:green;">Profile picture updated successfully!</span>';
            } else {
                $feedback = '<span style="color:red;">Failed to upload image.</span>';
            }
        } else {
            $feedback = '<span style="color:red;">Invalid file type. Only JPG, PNG, GIF allowed.</span>';
        }
    } else {
        $feedback = '<span style="color:red;">No file selected or upload error.</span>';
    }
}

// Handle remove profile picture
if (isset($_POST['remove_picture'])) {
    $default = 'images/default-avatar.png';
    $stmt = mysqli_prepare($conn, "UPDATE user SET profile_picture_url = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $default, $userId);
    mysqli_stmt_execute($stmt);
    $feedback = '<span style="color:green;">Profile picture removed.</span>';
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new !== $confirm) {
        $feedback = '<span style="color:red;">New passwords do not match.</span>';
    } else {
        // Check current password
        $stmt = mysqli_prepare($conn, "SELECT password FROM user WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if ($row && password_verify($current, $row['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt2 = mysqli_prepare($conn, "UPDATE user SET password = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt2, 'si', $hashed, $userId);
            mysqli_stmt_execute($stmt2);
            $feedback = '<span style="color:green;">Password changed successfully!</span>';
        } else {
            $feedback = '<span style="color:red;">Current password is incorrect.</span>';
        }
    }
}

// Get current profile picture
$stmt = mysqli_prepare($conn, "SELECT profile_picture_url FROM user WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$profilePic = $user['profile_picture_url'] ?? 'images/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-profile-container { max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 2rem; }
        .edit-profile-container h2 { color: #0056b3; margin-bottom: 1.5rem; }
        .profile-pic-preview { display: block; margin: 0 auto 1rem; width: 120px; height: 120px; border-radius: 50%; object-fit: cover; background: #eee; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #333; }
        input[type="file"] { margin-top: 0.5rem; }
        input[type="password"], input[type="text"] { width: 100%; padding: 0.7rem; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: #fff; border: none; padding: 0.8rem 2rem; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .feedback { text-align: center; margin-bottom: 1rem; }
        .option-btns { display: flex; flex-direction: column; gap: 1.5rem; margin: 2rem 0; }
        .option-btns a { display: block; text-align: center; background: #007bff; color: #fff; padding: 1rem; border-radius: 6px; text-decoration: none; font-size: 1.1rem; font-weight: 600; transition: background 0.2s; }
        .option-btns a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="edit-profile-container">
        <h2>Edit Profile</h2>
        <?php if ($feedback) echo '<div class="feedback">' . $feedback . '</div>'; ?>
        <?php if (!$view): ?>
            <div class="option-btns">
                <a href="edit-profile.php?view=picture">Edit Profile Picture</a>
                <a href="edit-profile.php?view=password">Change Password</a>
            </div>
        <?php elseif ($view === 'picture'): ?>
            <form method="post" enctype="multipart/form-data" style="margin-bottom:2rem;">
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic-preview">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                </div>
                <button type="submit" name="update_picture">Update Picture</button>
                <button type="submit" name="remove_picture" style="background:#dc3545;margin-left:1rem;">Remove Picture</button>
            </form>
            <a href="edit-profile.php" style="display:block;text-align:center;margin-top:1rem;">&larr; Back to options</a>
        <?php elseif ($view === 'password'): ?>
            <form method="post">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="change_password">Change Password</button>
            </form>
            <a href="edit-profile.php" style="display:block;text-align:center;margin-top:1rem;">&larr; Back to options</a>
        <?php endif; ?>
    </div>
</body>
</html> 