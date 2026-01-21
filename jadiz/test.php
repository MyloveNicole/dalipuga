<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XAMPP Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(0,0,0,0.2);
            padding: 40px;
            border-radius: 10px;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .status {
            font-size: 18px;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
        }
        .success {
            background: #27ae60;
        }
        .error {
            background: #e74c3c;
        }
        .info {
            background: #3498db;
            margin-top: 30px;
        }
        a {
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            background: #27ae60;
            border-radius: 5px;
            font-weight: bold;
        }
        a:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>XAMPP Connection Success!</h1>
        
        <div class="status success">
            <strong>Apache is Running</strong><br>
            You can access the system from your phone!
        </div>

        <div class="status success">
            <strong>PHP is Working</strong><br>
            Your server is responding correctly
        </div>

        <?php
        // Test Database Connection
        $db_success = false;
        try {
            $conn = new mysqli("localhost", "root", "", "barangay_system");
            if ($conn->connect_error) {
                echo '<div class="status error">
                        <strong>Database Error:</strong><br>' . $conn->connect_error . '
                      </div>';
            } else {
                $db_success = true;
                echo '<div class="status success">
                        <strong>Database Connected</strong><br>
                        Connected to: barangay_system
                      </div>';
                $conn->close();
            }
        } catch (Exception $e) {
            echo '<div class="status error">
                    <strong>Database Error:</strong><br>' . $e->getMessage() . '
                  </div>';
        }
        ?>

        <div class="status info">
            <strong>Your IP Address:</strong><br>
            Use this to access from phone: <strong>192.168.254.112</strong>
        </div>

        <a href="http://192.168.254.112/cadiz/auth/resident_login.php">Go to Resident Login</a>
        <a href="http://192.168.254.112/cadiz/resident_dashboard.php">Go to Dashboard</a>
    </div>
</body>
</html>
