<?php
include("includes/resident_auth_check.php");
include("includes/db_connect.php");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $trash_type = trim($_POST['trash_type'] ?? '');
    $trash_weight = floatval($_POST['trash_weight'] ?? 0);
    $resident_id = $_SESSION['resident_id'];
    $resident_name = $_SESSION['resident_name'] ?? '';
    $resident_email = $_SESSION['resident_email'] ?? '';
    
    // Get resident location and contact from database
    $stmt = $conn->prepare("SELECT location, contact_number FROM residents WHERE id = ?");
    $stmt->bind_param("i", $resident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resident_data = $result->fetch_assoc();
    $stmt->close();
    
    $resident_location = $resident_data['location'] ?? '';
    $resident_contact = $resident_data['contact_number'] ?? '';

    // Validation
    if (empty($subject) || empty($trash_type)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($subject) < 5) {
        $error = "Subject must be at least 5 characters long.";
    } else {
        try {
            // Insert letter into database
            $insert_stmt = $conn->prepare("INSERT INTO letters (resident_id, subject, message, resident_location, resident_name, resident_email, resident_contact, status, date_sent) VALUES (?, ?, ?, ?, ?, ?, ?, 'unread', NOW())");
            if (!$insert_stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            // Combine trash info into message
            $full_message = "Trash Type: " . htmlspecialchars($trash_type) . " | Weight: " . $trash_weight . " kg\n\n" . $message;
            
            $insert_stmt->bind_param("issssss", $resident_id, $subject, $full_message, $resident_location, $resident_name, $resident_email, $resident_contact);
            
            if ($insert_stmt->execute()) {
                $success = "Letter sent successfully to admin!";
                // Clear form
                $_POST['subject'] = '';
                $_POST['message'] = '';
                $_POST['trash_type'] = '';
                $_POST['trash_weight'] = '';
            } else {
                $error = "Error sending letter. Please try again.";
            }
            $insert_stmt->close();
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Letter to Admin - Dalipuga Cleanup System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .letter-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        @media (max-width: 768px) {
            .letter-container {
                margin: 20px 10px;
                padding: 20px;
            }
            .form-buttons {
                flex-direction: column !important;
            }
            .form-buttons .btn {
                width: 100%;
                margin-bottom: 10px !important;
                margin-left: 0 !important;
            }
            .form-control, input, textarea, select {
                font-size: 16px !important;
            }
        }
        .letter-header {
            margin-bottom: 30px;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 15px;
        }
        .letter-header h2 {
            color: #27ae60;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #27ae60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }
        .btn-send {
            background: #27ae60;
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            min-height: 48px;
            font-size: 16px;
        }
        .btn-send:hover {
            background: #229954;
            color: white;
        }
        .btn-back {
            background: #95a5a6;
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background: #7f8c8d;
            color: white;
            text-decoration: none;
        }
        .alert {
            border-radius: 5px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
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

    <!-- Main Content -->
    <div class="letter-container">
        <div class="letter-header">
            <h2>Send Letter to Admin</h2>
            <p class="text-muted mb-0">Communicate with the administrator</p>
        </div>

        <!-- Copy Link Section -->
        <div class="alert alert-info mb-4" role="alert">
            <strong>Share this link:</strong>
            <div class="input-group mt-2">
                <input type="text" class="form-control" id="copyLink" value="<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/cadiz/send_letter.php" readonly style="font-size: 14px;">
                <button class="btn btn-outline-info" type="button" onclick="copyToClipboard()" style="min-height: 48px; font-size: 14px;">
                    Copy
                </button>
            </div>
            <small class="d-block mt-2 text-muted">Share this link with others to report trash easily</small>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label" for="subject">Subject</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="subject"
                    name="subject" 
                    placeholder="Enter letter subject"
                    value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>"
                    minlength="5"
                    required>
                <small class="form-text text-muted">Minimum 5 characters</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="trash_type">Waste Type</label>
                    <select 
                        class="form-control" 
                        id="trash_type"
                        name="trash_type"
                        required>
                        <option value="">-- Select Waste Type --</option>
                        <option value="Recyclable - Plastic" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Recyclable - Plastic') ? 'selected' : '' ?>>Recyclable - Plastic</option>
                        <option value="Recyclable - Paper" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Recyclable - Paper') ? 'selected' : '' ?>>Recyclable - Paper</option>
                        <option value="Recyclable - Metal" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Recyclable - Metal') ? 'selected' : '' ?>>Recyclable - Metal</option>
                        <option value="Recyclable - Glass" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Recyclable - Glass') ? 'selected' : '' ?>>Recyclable - Glass</option>
                        <option value="Non-biodegradable" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Non-biodegradable') ? 'selected' : '' ?>>Non-biodegradable</option>
                        <option value="Biodegradable" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Biodegradable') ? 'selected' : '' ?>>Biodegradable</option>
                        <option value="Hazardous - Electronics" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Hazardous - Electronics') ? 'selected' : '' ?>>Hazardous - Electronics</option>
                        <option value="Hazardous - Chemicals" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Hazardous - Chemicals') ? 'selected' : '' ?>>Hazardous - Chemicals</option>
                        <option value="Mixed Waste" <?= (isset($_POST['trash_type']) && $_POST['trash_type'] === 'Mixed Waste') ? 'selected' : '' ?>>Mixed Waste</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="trash_weight">Weight (kg)</label>
                    <input 
                        type="number" 
                        class="form-control" 
                        id="trash_weight"
                        name="trash_weight" 
                        placeholder="Estimate weight in kilograms"
                        value="<?= isset($_POST['trash_weight']) ? htmlspecialchars($_POST['trash_weight']) : '' ?>"
                        step="0.1">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="message">Message</label>
                <textarea 
                    class="form-control" 
                    id="message"
                    name="message" 
                    placeholder="Write your message here (optional)..."
                    rows="8"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-send">Send Letter</button>
                <a href="resident_dashboard.php" class="btn btn-back">Back to Dashboard</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard() {
            const copyLink = document.getElementById('copyLink');
            copyLink.select();
            document.execCommand('copy');
            
            // Change button text temporarily
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = 'Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
    </script>
</body>
</html>