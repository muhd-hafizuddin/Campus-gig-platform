<?php
require_once '../customerdb.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    showMessageBox('Access denied. Admins only.', '../index.php');
}

// Real-time stats
$now = date('Y-m-01');
$lastMonth = date('Y-m-01', strtotime('-1 month'));
$nextMonth = date('Y-m-01', strtotime('+1 month'));

// Total users
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
$totalUsers = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Total jobs created
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job");
$totalJobs = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Total jobs taken (assigned or completed)
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE status IN ('assigned','completed')");
$totalJobsTaken = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Users registered this month and last month
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE created_at >= '$now' AND created_at < '$nextMonth'");
$usersThisMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE created_at >= '$lastMonth' AND created_at < '$now'");
$usersLastMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Jobs created this month and last month
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE created_at >= '$now' AND created_at < '$nextMonth'");
$jobsThisMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE created_at >= '$lastMonth' AND created_at < '$now'");
$jobsLastMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Jobs taken this month and last month
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE (status IN ('assigned','completed')) AND updated_at >= '$now' AND updated_at < '$nextMonth'");
$jobsTakenThisMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE (status IN ('assigned','completed')) AND updated_at >= '$lastMonth' AND updated_at < '$now'");
$jobsTakenLastMonth = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;

// Additional metrics for printout
// Jobs completed
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE status = 'completed'");
$jobsCompleted = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
// Jobs cancelled
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM job WHERE status = 'cancelled'");
$jobsCancelled = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
// Average time to completion (in days)
$res = mysqli_query($conn, "SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days FROM job WHERE status = 'completed' AND updated_at > created_at");
$row = mysqli_fetch_assoc($res);
$avgCompletion = (isset($row['avg_days']) && is_numeric($row['avg_days'])) ? round($row['avg_days'], 2) : 0;
// Most active categories
$res = mysqli_query($conn, "SELECT c.name, COUNT(*) as cnt FROM job j JOIN category c ON j.category_id = c.category_id GROUP BY c.category_id ORDER BY cnt DESC LIMIT 3");
$topCategories = [];
while ($row = mysqli_fetch_assoc($res)) $topCategories[] = $row;
// Average rating as poster/job taker
$res = mysqli_query($conn, "SELECT AVG(rating_as_poster) as avg_poster, AVG(rating_as_job_taker) as avg_taker FROM user");
$row = mysqli_fetch_assoc($res);
$avgPoster = (isset($row['avg_poster']) && is_numeric($row['avg_poster'])) ? round($row['avg_poster'],2) : 0;
$avgTaker = (isset($row['avg_taker']) && is_numeric($row['avg_taker'])) ? round($row['avg_taker'],2) : 0;
// Total reviews
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM review");
$totalReviews = ($row = mysqli_fetch_assoc($res)) ? $row['total'] : 0;
// Top 3 posters
$res = mysqli_query($conn, "SELECT name, jobs_posted FROM user ORDER BY jobs_posted DESC LIMIT 3");
$topPosters = [];
while ($row = mysqli_fetch_assoc($res)) $topPosters[] = $row;
// Top 3 job takers
$res = mysqli_query($conn, "SELECT name, jobs_taken FROM user ORDER BY jobs_taken DESC LIMIT 3");
$topTakers = [];
while ($row = mysqli_fetch_assoc($res)) $topTakers[] = $row;
// Top 3 most reviewed users
$res = mysqli_query($conn, "SELECT u.name, COUNT(r.review_id) as cnt FROM review r JOIN user u ON r.reviewee_id = u.user_id GROUP BY r.reviewee_id ORDER BY cnt DESC LIMIT 3");
$topReviewed = [];
while ($row = mysqli_fetch_assoc($res)) $topReviewed[] = $row;

// Recent activity (last 10 jobs or users)
$recent = [];
$res = mysqli_query($conn, "SELECT 'Job' as type, title as description, user_id, created_at, status FROM job ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) $recent[] = $row;
$res = mysqli_query($conn, "SELECT 'User' as type, name as description, user_id, created_at, 'Registered' as status FROM user ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) $recent[] = $row;
// Sort by created_at desc
usort($recent, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
$recent = array_slice($recent, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - JomBantu</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body, input, button, select, textarea, table, th, td, h1, h2, h3, h4, h5, h6, p, div, span, label {
            font-family: 'Inter', Arial, Helvetica, sans-serif !important;
        }
        .admin-dashboard-layout {
            min-height: 100vh;
            background: #f6f8fa;
        }
        .admin-topnav {
            width: 100%;
            background: #15395a;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 0.5rem 0;
            display: flex;
            justify-content: center;
        }
        .admin-nav-row {
            display: flex;
            gap: 1.2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .admin-nav-link {
            display: block;
            padding: 0.85rem 1.7rem;
            background: #205080;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(21,57,90,0.07);
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            letter-spacing: 0.01em;
            text-align: center;
            border: 2px solid transparent;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background: #fff;
            color: #15395a;
            border: 2px solid #205080;
            box-shadow: 0 4px 16px rgba(21,57,90,0.13);
        }
        .admin-dashboard-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2.5rem 2.5rem 2.5rem;
            background: #f6f8fa;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .print-btn {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            cursor: pointer;
            margin-bottom: 2.2rem;
            box-shadow: 0 2px 8px rgba(0,123,255,0.08);
            transition: background 0.18s;
            align-self: flex-end;
        }
        .print-btn:hover {
            background: #0056b3;
        }
        #print-stats { padding: 2rem; font-family: 'Inter', Arial, Helvetica, sans-serif !important; max-width: 1100px; margin: 0 auto; }
        #print-stats h1 { text-align: center; color: #15395a; margin-bottom: 1.2rem; font-size: 2.2rem; }
        #print-stats .print-meta { text-align: center; color: #444; font-size: 1.1rem; margin-bottom: 2.2rem; }
        #print-stats .stats-grid { display: flex; flex-wrap: wrap; gap: 2rem; justify-content: center; }
        #print-stats .stat-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 2rem 2.5rem; min-width: 220px; text-align: center; margin-bottom: 1.5rem; border: 2px solid #205080; }
        #print-stats .stat-card h2 { font-size: 1.2rem; color: #444; margin-bottom: 1rem; }
        #print-stats .stat-card .stat-value { font-size: 2.5rem; color: #007bff; font-weight: bold; }
        #print-stats .stat-card .stat-label { font-size: 1.1rem; color: #666; margin-top: 0.7rem; }
        .print-only { display: none; }
        @media print {
            body {
                background: #f6f8fa !important;
            }
            body * { visibility: hidden !important; }
            #print-stats, #print-stats * { visibility: visible !important; }
            #print-stats {
                position: static !important;
                left: auto !important;
                top: auto !important;
                transform: none !important;
                width: 90vw;
                max-width: 900px;
                background: #f6f8fa !important;
                box-shadow: 0 4px 32px rgba(0,0,0,0.13);
                border-radius: 18px;
                padding: 2.5rem 2.5rem 2rem 2.5rem !important;
                margin: 0 auto;
            }
            .print-btn, nav, header, .admin-dashboard-list, .admin-section-list, .admin-section-link, .admin-section-desc, .admin-sidebar, .admin-topnav { display: none !important; }
            .print-only { display: block !important; }
            .admin-dashboard-main { padding: 0 !important; }
            #print-stats .stats-grid {
                gap: 2.2rem;
                margin-top: 2.2rem;
            }
            #print-stats .stat-card {
                border: 2px solid #205080 !important;
                background: #fff !important;
                box-shadow: 0 2px 12px rgba(21,57,90,0.10) !important;
                border-radius: 14px !important;
                padding: 2.2rem 2.5rem !important;
                margin-bottom: 1.7rem !important;
            }
            #print-stats h1 {
                font-size: 2.3rem !important;
                margin-bottom: 1.5rem !important;
            }
            #print-stats .print-meta {
                font-size: 1.15rem !important;
                margin-bottom: 2.5rem !important;
            }
            #print-stats .recent-activity-card {
                background: #fff !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
                padding: 2rem 2.5rem !important;
                margin-top: 2.5rem !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            #print-stats .recent-activity-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 1rem !important;
                font-size: 1.05rem !important;
            }
            #print-stats .recent-activity-table th, #print-stats .recent-activity-table td {
                padding: 0.85rem 1.1rem !important;
                text-align: left !important;
                border: 1px solid #e3e8ee !important;
            }
            #print-stats .recent-activity-table th {
                background: #f4f6fb !important;
                color: #15395a !important;
                font-weight: 700 !important;
                border-bottom: 2px solid #205080 !important;
            }
            #print-stats .recent-activity-table tr {
                border-bottom: 1px solid #e3e8ee !important;
            }
            #print-stats .recent-activity-table tr:nth-child(even) {
                background: #f8f9fa !important;
            }
            #print-stats .recent-activity-table tr:last-child {
                border-bottom: none !important;
            }
        }
        .recent-activity-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 2rem 2.5rem;
            margin-top: 2.5rem;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }
        .recent-activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 1.05rem;
        }
        .recent-activity-table th, .recent-activity-table td {
            padding: 0.85rem 1.1rem;
            text-align: left;
        }
        .recent-activity-table th {
            background: #f4f6fb;
            color: #15395a;
            font-weight: 700;
            border-bottom: 2px solid #205080;
        }
        .recent-activity-table tr {
            border-bottom: 1px solid #e3e8ee;
        }
        .recent-activity-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .recent-activity-table tr:last-child {
            border-bottom: none;
        }
        .admin-topnav {
            background: #15395a !important;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
            margin-top: 0 !important;
            padding-top: 0.7rem !important;
            padding-bottom: 0.7rem !important;
            border-radius: 0 0 14px 14px !important;
        }
        .admin-nav-link {
            background: #15395a !important;
            color: #fff !important;
            border-radius: 8px !important;
            margin: 0 0.2rem !important;
            box-shadow: 0 2px 8px rgba(21,57,90,0.07) !important;
            border: 2px solid transparent !important;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s !important;
            font-weight: 500;
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
<div class="admin-dashboard-layout">
    <nav class="admin-topnav">
        <ul class="admin-nav-row">
            <li><a href="dashboard.php" class="admin-nav-link active">Dashboard</a></li>
            <li><a href="category-management.php" class="admin-nav-link">Category Management</a></li>
            <li><a href="job-management.php" class="admin-nav-link">Job Management</a></li>
            <li><a href="user-management.php" class="admin-nav-link">User Management</a></li>
            <li><a href="report-management.php" class="admin-nav-link">Report Management</a></li>
        </ul>
    </nav>
    <main class="admin-dashboard-main">
        <button class="print-btn" onclick="window.print()">🖨️ Print PDF Report</button>
        <div id="print-stats">
            <h1>JomBantu Monthly Platform Report</h1>
            <div class="print-meta">
                <span class="print-only">Printed by: <b><?php echo htmlspecialchars($_SESSION['name']); ?></b><br>Date: <b><?php echo date('d M Y, H:i'); ?></b></span>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <h2>Total Users</h2>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                </div>
                <div class="stat-card">
                    <h2>Total Jobs Created</h2>
                    <div class="stat-value"><?php echo $totalJobs; ?></div>
                </div>
                <div class="stat-card">
                    <h2>Total Jobs Taken</h2>
                    <div class="stat-value"><?php echo $totalJobsTaken; ?></div>
                </div>
                <div class="stat-card">
                    <h2>Users Registered</h2>
                    <div class="stat-label">This Month: <b><?php echo $usersThisMonth; ?></b></div>
                    <div class="stat-label">Last Month: <b><?php echo $usersLastMonth; ?></b></div>
                </div>
                <div class="stat-card">
                    <h2>Jobs Created</h2>
                    <div class="stat-label">This Month: <b><?php echo $jobsThisMonth; ?></b></div>
                    <div class="stat-label">Last Month: <b><?php echo $jobsLastMonth; ?></b></div>
                </div>
                <div class="stat-card">
                    <h2>Jobs Taken</h2>
                    <div class="stat-label">This Month: <b><?php echo $jobsTakenThisMonth; ?></b></div>
                    <div class="stat-label">Last Month: <b><?php echo $jobsTakenLastMonth; ?></b></div>
                </div>
            </div>
            <div class="advanced-metrics-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:2rem 2.5rem;margin:2.5rem auto;max-width:1000px;">
                <h2 style="margin-bottom:1.2rem;">Key Metrics & Insights</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.2rem;">
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e3f2fd;color:#1976d2;font-size:1.3rem;font-weight:bold;">✔️</span>
                        <div><span style="font-weight:600;">Jobs Completed</span><br><span style="font-size:1.1rem;"><?php echo $jobsCompleted; ?></span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#ffebee;color:#c62828;font-size:1.3rem;font-weight:bold;">❌</span>
                        <div><span style="font-weight:600;">Jobs Cancelled</span><br><span style="font-size:1.1rem;"><?php echo $jobsCancelled; ?></span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#fff3e0;color:#ef6c00;font-size:1.3rem;font-weight:bold;">⏱️</span>
                        <div><span style="font-weight:600;">Avg. Time to Completion</span><br><span style="font-size:1.1rem;"><?php echo $avgCompletion; ?> days</span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e8f5e9;color:#388e3c;font-size:1.3rem;font-weight:bold;">🏆</span>
                        <div><span style="font-weight:600;">Most Active Categories</span><br><span style="font-size:1.1rem;"><?php foreach($topCategories as $cat) echo htmlspecialchars($cat['name']).' ('.$cat['cnt'].' jobs)<br>'; ?></span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#ede7f6;color:#6a1b9a;font-size:1.3rem;font-weight:bold;">⭐</span>
                        <div><span style="font-weight:600;">Avg. Rating as Poster</span><br><span style="font-size:1.1rem;"><?php echo $avgPoster; ?></span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#fffde7;color:#fbc02d;font-size:1.3rem;font-weight:bold;">🌟</span>
                        <div><span style="font-weight:600;">Avg. Rating as Job Taker</span><br><span style="font-size:1.1rem;"><?php echo $avgTaker; ?></span></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e0f7fa;color:#00838f;font-size:1.3rem;font-weight:bold;">📝</span>
                        <div><span style="font-weight:600;">Total Reviews</span><br><span style="font-size:1.1rem;"><?php echo $totalReviews; ?></span></div>
                    </div>
                </div>
            </div>
            <div class="recent-activity-card">
                <h2 style="margin-bottom:1.2rem;">Recent Activity</h2>
                <table class="recent-activity-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>User ID</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['type']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo htmlspecialchars($item['user_id']); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($item['created_at'])); ?></td>
                            <td><span class="status-badge <?php echo ($item['status'] === 'active' || $item['status'] === 'Verified') ? 'status-active' : (($item['status'] === 'pending') ? 'status-pending' : 'status-suspended'); ?>"><?php echo htmlspecialchars(ucfirst($item['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<!-- Move print-only template outside main dashboard, just before </body> -->
<div id="print-template" class="print-only" style="display:none;">
    <div style="text-align:center;margin-bottom:1.5rem;">
        <img src="/GigPlatform/images/logo.png" alt="JomBantu Logo" style="height:70px;display:block;margin:0 auto 1.2rem auto;">
        <h1 style="color:#15395a;margin-bottom:0.7rem;font-size:2.2rem;">JomBantu Monthly Platform Report</h1>
        <div style="color:#444;font-size:1.1rem;margin-bottom:2.2rem;">
            Printed by: <b><?php echo htmlspecialchars($_SESSION['name']); ?></b><br>
            Date: <b><span id="print-date"></span></b>
        </div>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:1.2rem;justify-content:center;margin-bottom:2rem;">
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.05);padding:1.1rem 1.3rem;min-width:150px;text-align:center;border:2px solid #205080;margin-bottom:1rem;">
            <h2 style="font-size:1rem;color:#444;margin-bottom:0.5rem;">Total Users</h2>
            <div style="font-size:1.7rem;color:#007bff;font-weight:bold;"><?php echo $totalUsers; ?></div>
        </div>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.05);padding:1.1rem 1.3rem;min-width:150px;text-align:center;border:2px solid #205080;margin-bottom:1rem;">
            <h2 style="font-size:1rem;color:#444;margin-bottom:0.5rem;">Total Jobs Created</h2>
            <div style="font-size:1.7rem;color:#007bff;font-weight:bold;"><?php echo $totalJobs; ?></div>
        </div>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.05);padding:1.1rem 1.3rem;min-width:150px;text-align:center;border:2px solid #205080;margin-bottom:1rem;">
            <h2 style="font-size:1rem;color:#444;margin-bottom:0.5rem;">Total Jobs Taken</h2>
            <div style="font-size:1.7rem;color:#007bff;font-weight:bold;"><?php echo $totalJobsTaken; ?></div>
        </div>
        <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.05);padding:1.1rem 1.3rem;min-width:150px;text-align:center;border:2px solid #205080;margin-bottom:1rem;">
            <h2 style="font-size:1rem;color:#444;margin-bottom:0.5rem;">Users Registered</h2>
            <div style="font-size:0.95rem;color:#666;margin-top:0.3rem;">This Month: <b><?php echo $usersThisMonth; ?></b></div>
            <div style="font-size:0.95rem;color:#666;margin-top:0.3rem;">Last Month: <b><?php echo $usersLastMonth; ?></b></div>
        </div>
    </div>
    <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:1.5rem 1.5rem 1.2rem 1.5rem;margin:1.5rem auto 0 auto;max-width:900px;">
        <h2 style="margin-bottom:1.1rem;text-align:center;">Key Metrics & Insights</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.2rem;">
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e3f2fd;color:#1976d2;font-size:1.3rem;font-weight:bold;">✔️</span>
                <div><span style="font-weight:600;">Jobs Completed</span><br><span style="font-size:1.1rem;"><?php echo $jobsCompleted; ?></span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#ffebee;color:#c62828;font-size:1.3rem;font-weight:bold;">❌</span>
                <div><span style="font-weight:600;">Jobs Cancelled</span><br><span style="font-size:1.1rem;"><?php echo $jobsCancelled; ?></span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#fff3e0;color:#ef6c00;font-size:1.3rem;font-weight:bold;">⏱️</span>
                <div><span style="font-weight:600;">Avg. Time to Completion</span><br><span style="font-size:1.1rem;"><?php echo $avgCompletion; ?> days</span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e8f5e9;color:#388e3c;font-size:1.3rem;font-weight:bold;">🏆</span>
                <div><span style="font-weight:600;">Most Active Categories</span><br><span style="font-size:1.1rem;"><?php foreach($topCategories as $cat) echo htmlspecialchars($cat['name']).' ('.$cat['cnt'].' jobs)<br>'; ?></span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#ede7f6;color:#6a1b9a;font-size:1.3rem;font-weight:bold;">⭐</span>
                <div><span style="font-weight:600;">Avg. Rating as Poster</span><br><span style="font-size:1.1rem;"><?php echo $avgPoster; ?></span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#fffde7;color:#fbc02d;font-size:1.3rem;font-weight:bold;">🌟</span>
                <div><span style="font-weight:600;">Avg. Rating as Job Taker</span><br><span style="font-size:1.1rem;"><?php echo $avgTaker; ?></span></div>
            </div>
            <div style="background:#fff;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.07);border:2px solid #e3e8ee;padding:1.1rem 1.2rem;display:flex;align-items:center;gap:0.8rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#e0f7fa;color:#00838f;font-size:1.3rem;font-weight:bold;">📝</span>
                <div><span style="font-weight:600;">Total Reviews</span><br><span style="font-size:1.1rem;"><?php echo $totalReviews; ?></span></div>
            </div>
        </div>
        <div style="display:flex;gap:2.5rem;flex-wrap:wrap;margin-top:1.5rem;">
            <div style="flex:1;min-width:180px;">
                <b>Top 3 Posters</b><br>
                <ol><?php foreach($topPosters as $u) echo '<li>'.htmlspecialchars($u['name']).' ('.$u['jobs_posted'].' jobs)</li>'; ?></ol>
            </div>
            <div style="flex:1;min-width:180px;">
                <b>Top 3 Job Takers</b><br>
                <ol><?php foreach($topTakers as $u) echo '<li>'.htmlspecialchars($u['name']).' ('.$u['jobs_taken'].' jobs)</li>'; ?></ol>
            </div>
            <div style="flex:1;min-width:180px;">
                <b>Top 3 Most Reviewed Users</b><br>
                <ol><?php foreach($topReviewed as $u) echo '<li>'.htmlspecialchars($u['name']).' ('.$u['cnt'].' reviews)</li>'; ?></ol>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
  body * { display: none !important; }
  .print-only, .print-only * { display: block !important; visibility: visible !important; }
}
</style>
<script>
// Set realtime print date on print
function setPrintDate() {
  var d = new Date();
  var options = { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' };
  document.getElementById('print-date').textContent = d.toLocaleString('en-GB', options).replace(',', '');
}
window.addEventListener('beforeprint', setPrintDate);
// Also set on load in case user uses system dialog
setPrintDate();
</script>
</body>
</html> 