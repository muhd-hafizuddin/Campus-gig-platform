<?php
require_once '../customerdb.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    showMessageBox('Access denied. Admins only.', '../index.php');
}
// Handle job delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job'], $_POST['job_id'])) {
    $id = intval($_POST['job_id']);
    $sql = "DELETE FROM job WHERE job_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = 'Job deleted.';
    } else {
        $msg = 'Error deleting job.';
    }
}
// Fetch all jobs with category and poster info
$sql = "SELECT j.job_id, j.title, j.status, j.created_at, c.name AS category, u.name AS poster FROM job j LEFT JOIN category c ON j.category_id = c.category_id LEFT JOIN user u ON j.user_id = u.user_id ORDER BY j.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Job Management - JomBantu Admin</title>
    <link rel='stylesheet' href='../css/dashboard.css'>
    <link rel='stylesheet' href='../css/style.css'>
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .admin-table th, .admin-table td { padding: 0.8rem 1.1rem; border: 1px solid #e3e8ee; text-align: left; }
        .admin-table th { background: #f4f6fb; color: #15395a; font-weight: 700; }
        .admin-table tr:nth-child(even) { background: #f8f9fa; }
        .admin-table td.actions { text-align: center; }
        .admin-btn { padding: 0.4rem 1rem; border-radius: 6px; border: none; font-size: 1rem; cursor: pointer; margin-right: 0.5rem; }
        .admin-btn.edit { background: #007bff; color: #fff; }
        .admin-btn.delete { background: #e53935; color: #fff; }
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
<div class='admin-dashboard-layout'>
    <nav class='admin-topnav' style="background:#15395a; box-shadow:0 2px 12px rgba(0,0,0,0.08); margin-top: 0; padding-top: 0.7rem; padding-bottom: 0.7rem; border-radius: 0 0 14px 14px;">
        <ul class='admin-nav-row'>
            <li><a href='dashboard.php' class='admin-nav-link'>Dashboard</a></li>
            <li><a href='category-management.php' class='admin-nav-link'>Category Management</a></li>
            <li><a href='job-management.php' class='admin-nav-link active'>Job Management</a></li>
            <li><a href='user-management.php' class='admin-nav-link'>User Management</a></li>
            <li><a href='report-management.php' class='admin-nav-link'>Report Management</a></li>
        </ul>
    </nav>
    <main class='admin-dashboard-main'>
        <h1>Job Management</h1>
        <?php if (isset($msg)) echo "<div style='color:green;margin-bottom:1rem;'>$msg</div>"; ?>
        <table class='admin-table'>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Poster</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['poster']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                    <td class='actions'>
                        <!-- Edit action could link to a job edit page if implemented -->
                        <form method='post' style='display:inline;'>
                            <input type='hidden' name='job_id' value='<?php echo $row['job_id']; ?>'>
                            <button type='submit' name='delete_job' class='admin-btn delete' onclick="return confirm('Delete this job?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html> 