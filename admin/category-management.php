<?php
require_once '../customerdb.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    showMessageBox('Access denied. Admins only.', '../index.php');
}
function redirectWithMsg($msg) {
    header('Location: category-management.php?msg=' . urlencode($msg));
    exit();
}
// Handle add, edit, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        if ($name) {
            $sql = "INSERT INTO category (name, description, created_at) VALUES (?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $name, $desc);
            if (mysqli_stmt_execute($stmt)) {
                redirectWithMsg('Category added.');
            } else {
                redirectWithMsg('Error adding category.');
            }
        }
    } elseif (isset($_POST['edit_category'], $_POST['category_id'])) {
        $id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $sql = "UPDATE category SET name=?, description=? WHERE category_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $desc, $id);
        if (mysqli_stmt_execute($stmt)) {
            redirectWithMsg('Category updated.');
        } else {
            redirectWithMsg('Error updating category.');
        }
    } elseif (isset($_POST['delete_category'], $_POST['category_id'])) {
        $id = intval($_POST['category_id']);
        $sql = "DELETE FROM category WHERE category_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            redirectWithMsg('Category deleted.');
        } else {
            redirectWithMsg('Error deleting category.');
        }
    }
    redirectWithMsg('Invalid action.');
}
// Fetch all categories
$sql = "SELECT * FROM category ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Category Management - JomBantu Admin</title>
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
        .admin-btn.save { background: #388e3c; color: #fff; }
        .admin-btn.cancel { background: #888; color: #fff; }
        .admin-form-row { display: flex; gap: 1rem; margin-bottom: 1.2rem; }
        .admin-form-row input, .admin-form-row textarea { padding: 0.5rem; border-radius: 5px; border: 1px solid #ccc; font-size: 1rem; }
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
    <nav class='admin-topnav'>
        <ul class='admin-nav-row'>
            <li><a href='dashboard.php' class='admin-nav-link'>Dashboard</a></li>
            <li><a href='category-management.php' class='admin-nav-link active'>Category Management</a></li>
            <li><a href='job-management.php' class='admin-nav-link'>Job Management</a></li>
            <li><a href='user-management.php' class='admin-nav-link'>User Management</a></li>
            <li><a href='report-management.php' class='admin-nav-link'>Report Management</a></li>
        </ul>
    </nav>
    <main class='admin-dashboard-main'>
        <h1>Category Management</h1>
        <?php if (isset($_GET['msg'])) echo "<div style='color:green;margin-bottom:1rem;'>".htmlspecialchars($_GET['msg'])."</div>"; ?>
        <form method='post' style='margin-bottom:2rem;'>
            <div class='admin-form-row'>
                <input type='text' name='name' placeholder='Category Name' required>
                <textarea name='description' placeholder='Description' rows='1'></textarea>
                <button type='submit' name='add_category' class='admin-btn save'>Add Category</button>
            </div>
        </form>
        <table class='admin-table'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <form method='post'>
                        <td><input type='text' name='name' value='<?php echo htmlspecialchars($row['name']); ?>' required></td>
                        <td><textarea name='description' rows='1'><?php echo htmlspecialchars($row['description']); ?></textarea></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                        <td class='actions'>
                            <input type='hidden' name='category_id' value='<?php echo $row['category_id']; ?>'>
                            <button type='submit' name='edit_category' class='admin-btn edit'>Save</button>
                            <button type='submit' name='delete_category' class='admin-btn delete' onclick="return confirm('Delete this category?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html> 