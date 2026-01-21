<?php
session_start();
include("../includes/db_connect.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT Admin_Id, Username, Password FROM admin WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Check if password is hashed (using password_hash) or plain text
            if (password_verify($password, $row['Password']) || $row['Password'] === $password) {
                $_SESSION['admin_id'] = $row['Admin_Id'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['role'] = 'admin';
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dalipuga Cleanup Management System</title>
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Dalipuga Cleanup</h2>
            <p>Management System Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username"
                    name="username" 
                    placeholder="Enter your username" 
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password"
                    name="password" 
                    placeholder="Enter your password" 
                    required>
            </div>

            <button type="submit" class="btn btn-login">Login</button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                Don't have an account? <a href="register.php" style="color: #27ae60; font-weight: 500;">Register here</a>
            </small>
        </div>

        <div class="text-center mt-2">
            <small class="text-muted">
                Resident? <a href="resident_login.php" style="color: #27ae60; font-weight: 500;">Login as Resident</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
