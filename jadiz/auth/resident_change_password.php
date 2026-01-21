<?php
session_start();
include("../includes/db_connect.php");

if(!isset($_SESSION['resident_id'])){
    header("Location: resident_login.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $resident_id = $_SESSION['resident_id'];

    if (!$current_password || !$new_password || !$confirm_password) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM residents WHERE id = ?");
        $stmt->bind_param("i", $resident_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Check both hashed and plain text passwords for backward compatibility
        $password_valid = password_verify($current_password, $row['password']) || $row['password'] === $current_password;

        if ($password_valid) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $conn->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_new_password, $resident_id);
            
            if ($stmt->execute()) {
                $message = "Password changed successfully!";
            } else {
                $error = "Error updating password. Please try again.";
            }
            $stmt->close();
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Dalipuga Cleanup System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 {
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #27ae60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }
        .btn-submit {
            background: #27ae60;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 5px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: #229954;
            color: white;
        }
        .btn-back {
            background: #95a5a6;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 5px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #7f8c8d;
            color: white;
            text-decoration: none;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2>Change Password</h2>
            <p class="text-muted">Update your account password</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label" for="current_password">Current Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="current_password"
                    name="current_password" 
                    placeholder="Enter current password" 
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="new_password">New Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="new_password"
                    name="new_password" 
                    placeholder="Enter new password (min 6 characters)" 
                    minlength="6"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="confirm_password"
                    name="confirm_password" 
                    placeholder="Confirm new password" 
                    minlength="6"
                    required>
            </div>

            <button type="submit" class="btn btn-submit">Change Password</button>
            <a href="../resident_dashboard.php" class="btn-back">Back to Dashboard</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
