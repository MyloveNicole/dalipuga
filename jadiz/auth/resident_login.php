<?php
session_start();
include("../includes/db_connect.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM residents WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Check if password is hashed or plain text
                if (password_verify($password, $row['password']) || $row['password'] === $password) {
                    $_SESSION['resident_id'] = $row['id'];
                    $_SESSION['resident_name'] = $row['first_name'] . ' ' . $row['last_name'];
                    $_SESSION['resident_email'] = $row['email'];
                    $_SESSION['role'] = 'resident';
                    header("Location: ../resident_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
            $stmt->close();
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
    <title>Resident Login - Dalipuga Cleanup Management System</title>
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
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 14px;
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
        .btn-login {
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
        .btn-login:hover {
            background: #229954;
            color: white;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
        .login-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        .login-links .form-link {
            display: block;
            margin: 12px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .login-links .form-link a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .login-links .form-link a:hover {
            color: #229954;
            text-decoration: underline;
        }
        .login-links .form-link:first-child {
            margin-bottom: 15px;
        }
        .divider {
            margin: 15px 0;
            color: #7f8c8d;
            text-align: center;
            font-size: 12px;
        }
        .modal-content {
            border-radius: 10px;
            border: none;
        }
        .modal-header {
            background: #27ae60;
            color: white;
            border-radius: 10px 10px 0 0;
            border: none;
        }
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .pin-container {
            text-align: center;
        }
        .pin-input {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: 600;
            border: 2px solid #27ae60 !important;
            padding: 15px !important;
        }
        .pin-input:focus {
            border-color: #229954 !important;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25) !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Dalipuga Cleanup</h2>
            <p>Resident Login Portal</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    required
                    autocomplete="email">
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password"
                    name="password" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-login">Login</button>
        </form>

        <div class="login-links">
            <div class="form-link">
                Don't have an account? <a href="resident_register.php">Register here</a>
            </div>
            <div class="form-link">
                Admin? <a href="#" data-bs-toggle="modal" data-bs-target="#adminPinModal">Login as Administrator</a>
            </div>
        </div>
    </div>

    <!-- Admin PIN Protection Modal -->
    <div class="modal fade" id="adminPinModal" tabindex="-1" aria-labelledby="adminPinLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminPinLabel">🔒 Admin Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="pin-container">
                        <p class="mb-4">Enter PIN to access administrator login</p>
                        <input 
                            type="password" 
                            class="form-control pin-input" 
                            id="pinInput"
                            placeholder="••••"
                            maxlength="4"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div id="pinMessage" style="margin-top: 15px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="verifyAdminPin()">Unlock</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        // Admin PIN verification
        const ADMIN_PIN = '5500'; // Change this PIN to your desired admin PIN
        
        function verifyAdminPin() {
            const pinInput = document.getElementById('pinInput');
            const pinMessage = document.getElementById('pinMessage');
            const enteredPin = pinInput.value;
            
            if (enteredPin.length < 4) {
                pinMessage.innerHTML = '<div class="alert alert-danger">PIN must be 4 digits</div>';
                return;
            }
            
            if (enteredPin === ADMIN_PIN) {
                pinMessage.innerHTML = '<div class="alert alert-success"><strong>✓ Correct!</strong> Redirecting...</div>';
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 800);
            } else {
                pinMessage.innerHTML = '<div class="alert alert-danger"><strong>✗ Incorrect PIN</strong> Try again</div>';
                pinInput.value = '';
                pinInput.focus();
            }
        }
        
        // Allow Enter key to verify PIN
        document.getElementById('pinInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyAdminPin();
            }
        });
        
        // Clear PIN input when modal opens
        document.getElementById('adminPinModal')?.addEventListener('show.bs.modal', function() {
            document.getElementById('pinInput').value = '';
            document.getElementById('pinMessage').innerHTML = '';
            document.getElementById('pinInput').focus();
        });
    </script>
</body>
</html>
