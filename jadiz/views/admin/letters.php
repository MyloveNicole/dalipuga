<?php
/**
 * Admin Letters Management View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letters Management - Dalipuga Cleanup Management System</title>
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
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-dashboard"></i> Dashboard
                    </a>
                    <a class="nav-link" href="residents.php">
                        <i class="fas fa-users"></i> Residents
                    </a>
                    <a class="nav-link" href="inventory.php">
                        <i class="fas fa-box"></i> Inventory
                    </a>
                    <a class="nav-link active" href="letters.php">
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
                            <h5 class="mb-0">Letters Management</h5>
                        </div>
                        <div class="col text-end">
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#sendLetterModal">
                                <i class="fas fa-plus"></i> Send Letter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="main-content">
                    <div class="page-title">
                        <h3><i class="fas fa-envelope"></i> All Letters</h3>
                    </div>

                    <!-- Letters Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Recipient</th>
                                            <th>Subject</th>
                                            <th>Date Sent</th>
                                            <th>Status</th>
                                            <th>Read Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($letters) && count($letters) > 0): ?>
                                            <?php foreach ($letters as $letter): ?>
                                                <tr>
                                                    <td>#<?= htmlspecialchars($letter['id'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($letter['recipient_name'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($letter['subject'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($letter['date_sent'] ?? 'N/A') ?></td>
                                                    <td><span class="badge bg-success">Sent</span></td>
                                                    <td>
                                                        <?php if ($letter['is_read'] ?? 0): ?>
                                                            <span class="badge bg-info">Read</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Unread</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No letters found</td>
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

    <!-- Send Letter Modal -->
    <div class="modal fade" id="sendLetterModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Letter to Residents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Recipient(s)</label>
                            <select class="form-control" required>
                                <option value="">-- Choose Resident --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Send Letter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
