<?php
session_start();
include("../includes/db_connect.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $contact_number = trim($_POST['contact_number']);

    // Validation
    if (!$username || !$email || !$password || !$confirm_password) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT Admin_Id FROM admin WHERE Username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            // Check if email already exists
            $email_stmt = $conn->prepare("SELECT Admin_Id FROM admin WHERE Email = ?");
            $email_stmt->bind_param("s", $email);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();

            if ($email_result->num_rows > 0) {
                $error = "Email already registered. Please use another.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new admin/user
                $insert_stmt = $conn->prepare("INSERT INTO admin (Username, Email, Contact_Number, Password) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("ssss", $username, $email, $contact_number, $hashed_password);

                if ($insert_stmt->execute()) {
                    $success = "Account created successfully! Redirecting to login...";
                    // Redirect to login after 2 seconds
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Error creating account. Please try again.";
                }
                $insert_stmt->close();
            }
            $email_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Dalipuga Cleanup Management System</title>
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
        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #27ae60;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .register-header p {
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
        .btn-register {
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
        .btn-register:hover {
            background: #229954;
            color: white;
        }
        .btn-register:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Dalipuga Cleanup</h2>
            <p>Create New Account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username"
                    name="username" 
                    placeholder="Choose a username"
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                    minlength="3"
                    required>
                <div class="password-requirements">Min 3 characters</div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="contact_number">Contact Number</label>
                <input
                    type="tel"
                    name="contact_number"
                    class="form-control"
                    id="contact_number"
                    placeholder="09171234567"
                    value="<?= isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : '' ?>"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="11">
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password"
                    name="password" 
                    placeholder="Enter a strong password" 
                    minlength="6"
                    required>
                <div class="password-requirements">Min 6 characters</div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="confirm_password"
                    name="confirm_password" 
                    placeholder="Re-enter your password" 
                    minlength="6"
                    required>
            </div>

            <button type="submit" class="btn btn-register">Create Account</button>
        </form>

        <div class="login-link">
            <small class="text-muted">
                Already have an account? <a href="login.php">Login here</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time password match validation
        document.getElementById('confirm_password').addEventListener('change', function() {
            if (this.value !== document.getElementById('password').value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
