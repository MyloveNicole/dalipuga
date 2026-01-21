<?php
session_start();
include("includes/db_connect.php");

if(!isset($_SESSION['resident_id'])){
    header("Location: auth/resident_login.php");
    exit;
}

$resident_id = $_SESSION['resident_id'];

// Get resident information
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, contact_number, location, date_added FROM residents WHERE id = ?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
$resident = $result->fetch_assoc();
$stmt->close();

// If resident not found, logout
if (!$resident) {
    session_destroy();
    header("Location: auth/resident_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard - Dalipuga Cleanup Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        .appointment-card {
            border-left: 4px solid #27ae60;
            transition: transform 0.3s;
        }
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .schedule-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: 500;
            margin: 5px;
        }
        .schedule-monday {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .schedule-wednesday {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .schedule-saturday {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        /* Map removed from resident dashboard as requested */
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="resident_dashboard.php">
                Dalipuga Cleanup Management System
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#residentNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="residentNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="resident_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="send_letter.php">Send Letter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/resident_change_password.php">Change Password</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/resident_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid px-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-success fw-bold">Welcome, <?= htmlspecialchars($resident['first_name'] ?? '') ?>!</h1>
                <p class="text-muted mb-0">Resident Dashboard</p>
            </div>
        </div>

        <!-- Profile Information Card -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0 fw-bold">Your Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fw-semibold text-success">Name</label>
                            <p class="text-dark"><?= htmlspecialchars(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-success">Email</label>
                            <p class="text-dark"><?= htmlspecialchars($resident['email'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-success">Contact Number</label>
                            <p class="text-dark"><?= htmlspecialchars($resident['contact_number'] ?? 'N/A') ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-success">Location/Address</label>
                            <p class="text-dark"><?= htmlspecialchars($resident['location'] ?? '') ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-success">Member Since</label>
                            <p class="text-dark"><?= htmlspecialchars(isset($resident['date_added']) ? date("M d, Y", strtotime($resident['date_added'])) : '') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0 fw-bold">System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fw-semibold text-info">System Name</label>
                            <p class="text-dark">Dalipuga Cleanup Management System</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-info">Role</label>
                            <p class="text-dark"><span class="badge bg-info">Resident</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold text-info">Session Status</label>
                            <p class="text-dark"><span class="badge bg-success">Active</span></p>
                        </div>
                        <div class="alert alert-info mt-4">
                            <strong>Welcome!</strong> You can now view your profile information and manage your account settings. Use the navigation menu to access more features.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-success">About Dalipuga Cleanup System</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    The Dalipuga Cleanup Management System is designed to help manage cleanup activities, track inventory, and coordinate with residents for a cleaner and healthier community.
                </p>
                <p class="text-muted mb-0">
                    As a resident, you can access your profile information and stay updated with community cleanup schedules and initiatives.
                </p>
            </div>
        </div>

        <!-- Appointment Schedule Card -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 fw-bold">Cleanup Appointment Schedule</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Our cleanup team operates on the following schedule:</p>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card appointment-card border-0 h-100">
                            <div class="card-body">
                                <span class="schedule-badge schedule-monday">Monday</span>
                                <h6 class="mt-3 fw-bold text-dark">Monday Schedule</h6>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-clock"></i> 8:00 AM - 12:00 PM
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt"></i> All zones available for cleanup
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card appointment-card border-0 h-100">
                            <div class="card-body">
                                <span class="schedule-badge schedule-wednesday">Wednesday</span>
                                <h6 class="mt-3 fw-bold text-dark">Wednesday Schedule</h6>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-clock"></i> 9:00 AM - 1:00 PM
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt"></i> All zones available for cleanup
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card appointment-card border-0 h-100">
                            <div class="card-body">
                                <span class="schedule-badge schedule-saturday">Saturday</span>
                                <h6 class="mt-3 fw-bold text-dark">Saturday Schedule</h6>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-clock"></i> 7:00 AM - 12:00 PM
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt"></i> All zones available for cleanup
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Tip:</strong> Send us a letter with your location and waste type to schedule a pickup during these times!
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-4 mt-5">
        <div class="container-fluid">
            <p class="text-muted mb-0">&copy; 2026 Dalipuga Cleanup Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
            .card {
                margin-bottom: 15px;
            }
            .btn-success {
                min-height: 48px;
                font-size: 16px;
            }
            input, select, textarea {
                font-size: 16px !important;
            }
            .row {
                margin-left: -5px;
                margin-right: -5px;
            }
            .col-md-4, .col-md-6 {
                padding-left: 5px;
                padding-right: 5px;
            }
        }
    </style>
</body>
</html>