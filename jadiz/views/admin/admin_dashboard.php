<?php
/**
 * Admin Dashboard View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dalipuga Cleanup Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            background: #27ae60;
            min-height: 100vh;
            padding: 20px 0;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            margin: 5px 0;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            border-left-color: white;
        }
        .top-navbar {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }
        .stat-card h5 {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #27ae60;
        }
        .stat-card.blue {
            border-left-color: #3498db;
        }
        .stat-card.blue .number {
            color: #3498db;
        }
        .stat-card.orange {
            border-left-color: #e67e22;
        }
        .stat-card.orange .number {
            color: #e67e22;
        }
        .stat-card.red {
            border-left-color: #e74c3c;
        }
        .stat-card.red .number {
            color: #e74c3c;
        }
        .main-content {
            padding: 20px;
        }
        .page-title {
            margin-bottom: 30px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="p-3 border-bottom border-light">
                    <h5 class="mb-0"><i class="fas fa-leaf"></i> Dalipuga Admin</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="admin_dashboard.php">
                        <i class="fas fa-dashboard"></i> Dashboard
                    </a>
                    <a class="nav-link" href="residents.php">
                        <i class="fas fa-users"></i> Residents
                    </a>
                    <a class="nav-link" href="inventory.php">
                        <i class="fas fa-box"></i> Inventory
                    </a>
                    <a class="nav-link" href="letters.php">
                        <i class="fas fa-envelope"></i> Letters
                    </a>
                    <a class="nav-link" href="activities.php">
                        <i class="fas fa-history"></i> Activities
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <hr class="bg-light">
                    <a class="nav-link" href="../../auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <!-- Top Navbar -->
                <div class="top-navbar">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h5>
                        </div>
                        <div class="col text-end">
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="main-content">
                    <div class="page-title">
                        <h3><i class="fas fa-dashboard"></i> Dashboard Overview</h3>
                        <p class="text-muted">System Statistics and Activities</p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h5>Total Residents</h5>
                                <div class="number"><?= isset($stats['total_residents']) ? $stats['total_residents'] : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card blue">
                                <h5>Active Inventories</h5>
                                <div class="number"><?= isset($stats['active_inventories']) ? $stats['active_inventories'] : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card orange">
                                <h5>Pending Letters</h5>
                                <div class="number"><?= isset($stats['pending_letters']) ? $stats['pending_letters'] : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card red">
                                <h5>System Activities</h5>
                                <div class="number"><?= isset($stats['total_activities']) ? $stats['total_activities'] : 0 ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-list"></i> Recent Activities</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Timestamp</th>
                                                    <th>User</th>
                                                    <th>Action</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($recent_activities) && count($recent_activities) > 0): ?>
                                                    <?php foreach ($recent_activities as $activity): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($activity['timestamp'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($activity['user'] ?? 'N/A') ?></td>
                                                            <td><span class="badge bg-info"><?= htmlspecialchars($activity['action'] ?? 'N/A') ?></span></td>
                                                            <td><?= htmlspecialchars($activity['details'] ?? 'N/A') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">No recent activities</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
