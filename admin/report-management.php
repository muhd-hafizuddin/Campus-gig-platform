<?php
require_once '../customerdb.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    showMessageBox('Access denied. Admins only.', '../index.php');
}

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['report_id']) && isset($_POST['new_status'])) {
    $report_id = intval($_POST['report_id']);
    $new_status = $_POST['new_status'];
    $admin_id = $_SESSION['id'];
    $sql = "UPDATE report SET status = ?, handled_by = ? WHERE report_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sii', $new_status, $admin_id, $report_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: report-management.php');
    exit();
}
// Fetch all reports
$sql = "SELECT r.*, u1.name as reporter_name, u2.name as reported_name, j.title as job_title, a.name as admin_name
        FROM report r
        LEFT JOIN user u1 ON r.reporter_id = u1.user_id
        LEFT JOIN user u2 ON r.reported_user_id = u2.user_id
        LEFT JOIN job j ON r.job_id = j.job_id
        LEFT JOIN user a ON r.handled_by = a.user_id
        ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Management - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .report-table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .report-table th, .report-table td { border: 1px solid #eee; padding: 10px; text-align: left; }
        .report-table th { background: #f8f9fa; }
        .report-table tr:nth-child(even) { background: #f4f8fb; }
        .btn-status { background: #007bff; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; margin-right: 6px; }
        .status-pending { color: #ffc107; }
        .status-investigating { color: #007bff; }
        .status-resolved { color: #28a745; }
        .status-dismissed { color: #dc3545; }
        @media print {
            body * { visibility: hidden !important; }
            .report-table, .report-table * { visibility: visible !important; }
            .report-table { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
            .btn-status, select, form, header, nav, .container > h1, .admin-section-list, .admin-dashboard-list, .print-btn { display: none !important; }
            .report-table th:last-child, .report-table td:last-child { display: none !important; }
        }
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
        <li><a href='user-management.php' class='admin-nav-link'>User Management</a></li>
        <li><a href='report-management.php' class='admin-nav-link active'>Report Management</a></li>
    </ul>
</nav>
<main class="container">
    <h1>Report Management</h1>
    <table class="report-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Reporter</th>
                <th>Reported User</th>
                <th>Job</th>
                <th>Reason</th>
                <th>Created</th>
                <th>Handled By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $r['report_id']; ?></td>
                <td class="status-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></td>
                <td><?php echo htmlspecialchars($r['reporter_name']); ?></td>
                <td><?php echo htmlspecialchars($r['reported_name']); ?></td>
                <td><?php echo htmlspecialchars($r['job_title']); ?></td>
                <td><?php echo htmlspecialchars($r['reason']); ?></td>
                <td><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                <td><?php echo $r['admin_name'] ? htmlspecialchars($r['admin_name']) : '-'; ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="report_id" value="<?php echo $r['report_id']; ?>">
                        <select name="new_status">
                            <option value="pending" <?php if ($r['status']==='pending') echo 'selected'; ?>>Pending</option>
                            <option value="investigating" <?php if ($r['status']==='investigating') echo 'selected'; ?>>Investigating</option>
                            <option value="resolved" <?php if ($r['status']==='resolved') echo 'selected'; ?>>Resolved</option>
                            <option value="dismissed" <?php if ($r['status']==='dismissed') echo 'selected'; ?>>Dismissed</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-status">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</main>
</body>
</html> 