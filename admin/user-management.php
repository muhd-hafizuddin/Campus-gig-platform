<?php
require_once '../customerdb.php';
// Only allow admins
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    showMessageBox('Access denied. Admins only.', '../index.php');
}

// Handle suspend/unsuspend actions
if (isset($_POST['toggle_active']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = intval($_POST['new_status']);
    $sql = "UPDATE user SET is_active = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $new_status, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: user-management.php');
    exit();
}

// Fetch all users except admins
$sql = "SELECT user_id, name, email, phone_number, is_active, role, created_at FROM user WHERE role != 'admin' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .user-table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .user-table th, .user-table td { border: 1px solid #eee; padding: 10px; text-align: left; }
        .user-table th { background: #f8f9fa; }
        .user-table tr:nth-child(even) { background: #f4f8fb; }
        .btn-suspend { background: #dc3545; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; }
        .btn-unsuspend { background: #28a745; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; }
        .admin-topnav {
            background: #15395a !important;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
            margin-top: 0 !important;
            padding-top: 1.2rem !important;
            padding-bottom: 1.2rem !important;
            border-radius: 0 0 14px 14px !important;
            display: flex;
            justify-content: center;
            width: 100% !important;
        }
        .admin-nav-row {
            display: flex;
            gap: 2.2rem;
            list-style: none;
            margin: 0;
            padding: 0;
            justify-content: center;
        }
        .admin-nav-link {
            background: #15395a !important;
            color: #fff !important;
            border-radius: 8px !important;
            margin: 0 0.2rem !important;
            box-shadow: 0 2px 8px rgba(21,57,90,0.07) !important;
            border: 2px solid transparent !important;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s !important;
            font-weight: 600;
            font-size: 1.15rem;
            padding: 0.85rem 2.2rem;
            text-align: center;
            letter-spacing: 0.01em;
        }
        .admin-nav-link.active, .admin-nav-link:focus {
            background: #007bff !important;
            color: #fff !important;
            border: 2px solid #0056b3 !important;
            box-shadow: 0 4px 16px rgba(0,123,255,0.13) !important;
            font-weight: 700;
            z-index: 1;
        }
        .admin-nav-link:hover:not(.active) {
            background: #205080 !important;
            color: #fff !important;
        }
        .admin-dashboard-layout {
            margin-top: 0 !important;
        }
    </style>
</head>
<body>
<?php $is_subdirectory = true; include '../header.php'; ?>
<nav class='admin-topnav'>
    <ul class='admin-nav-row'>
        <li><a href='dashboard.php' class='admin-nav-link'>Dashboard</a></li>
        <li><a href='category-management.php' class='admin-nav-link'>Category Management</a></li>
        <li><a href='job-management.php' class='admin-nav-link'>Job Management</a></li>
        <li><a href='user-management.php' class='admin-nav-link active'>User Management</a></li>
        <li><a href='report-management.php' class='admin-nav-link'>Report Management</a></li>
    </ul>
</nav>
<main class="container">
    <h1>User Management</h1>
    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo $user['is_active'] ? '<span style="color:#28a745;">Active</span>' : '<span style="color:#dc3545;">Inactive</span>'; ?></td>
                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <?php if ($user['is_active']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <input type="hidden" name="new_status" value="0">
                            <button type="submit" name="toggle_active" class="btn-suspend" onclick="return confirm('Suspend this user?');">Suspend</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <input type="hidden" name="new_status" value="1">
                            <button type="submit" name="toggle_active" class="btn-unsuspend" onclick="return confirm('Unsuspend this user?');">Unsuspend</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</main>
</body>
</html> 