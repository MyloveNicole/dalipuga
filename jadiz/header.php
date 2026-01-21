<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dalipuga Cleanup Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    
</head>
<body>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const contactInput = document.querySelector("input[name='contact_number']");
    
    if (contactInput) {
        contactInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid px-4">

        <a class="navbar-brand" href="index.php">
            Dalipuga Cleanup Management System
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="resident.php">Residents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="letters.php">Letters</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admins.php">Administrators</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/change_password.php">Change Password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<!-- Page Content Start -->
<div class="container-fluid px-4 mt-4">