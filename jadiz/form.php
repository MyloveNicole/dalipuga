<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">

            <!-- Page Card -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success">Add New Resident</h5>
                    <small class="text-muted">Fill in the resident’s basic information below</small>
                </div>

                <div class="card-body p-4">

                    <?php
                    if (isset($_POST['add_resident'])) {

                        $first_name     = trim($_POST['first_name']);
                        $last_name      = trim($_POST['last_name']);
                        $email          = trim($_POST['email']);
                        $location       = trim($_POST['location']);
                        $contact_number = trim($_POST['contact_number']);

                        if ($first_name && $last_name && $email && $location && $contact_number) {

                            $stmt = $conn->prepare(
                                "INSERT INTO residents (first_name, last_name, email, location, contact_number, password)
                                 VALUES (?, ?, ?, ?, ?, ?)"
                            );
                            $hashed_password = password_hash("password123", PASSWORD_DEFAULT);
                            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $location, $contact_number, $hashed_password);

                            if ($stmt->execute()) {
                                echo "
                                <div class='alert alert-success text-center'>
                                    Resident added successfully. Redirecting...
                                </div>
                                <script>
                                    setTimeout(function(){
                                        window.location.href = 'resident.php';
                                    }, 2000);
                                </script>";
                            } else {
                                echo "<div class='alert alert-danger'>Database error. Please try again.</div>";
                            }

                            $stmt->close();

                        } else {
                            echo "<div class='alert alert-warning'>All fields are required.</div>";
                        }
                    }
                    ?>

                    <!-- Resident Form -->
                    <form action="" method="POST" novalidate>

                        <div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">First Name</label>
        <input
            type="text"
            name="first_name"
            class="form-control"
            placeholder="Enter first name"
            required
            pattern="[A-Za-z\s]+"
            title="Letters only"
            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Last Name</label>
        <input
            type="text"
            name="last_name"
            class="form-control"
            placeholder="Enter last name"
            required
            pattern="[A-Za-z\s]+"
            title="Letters only"
            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Email Address</label>
    <input
        type="email"
        name="email"
        class="form-control"
        placeholder="e.g. resident@example.com"
        required>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Address</label>
    <input
        type="text"
        name="location"
        class="form-control"
        placeholder="e.g. Purok 5, Dalipuga"
        required
        pattern="[A-Za-z0-9\s,.-]+"
        title="Only letters, numbers, spaces, commas, periods, and hyphens are allowed"
        oninput="this.value = this.value.replace(/[^A-Za-z0-9\s,.-]/g, '')">
</div>


                        <div class="mb-4">
                            <label class="form-label fw-semibold">Contact Number</label>
                            <input
                                type="tel"
                                name="contact_number"
                                class="form-control"
                                placeholder="09171234567"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="11"
                                required>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="resident.php" class="btn btn-light">
                                Cancel
                            </a>
                            <button type="submit" name="add_resident" class="btn btn-success px-4">
                                Save Resident
                            </button>
                        </div>

                    </form>
                    <!-- End Form -->

                </div>
            </div>

        </div>
    </div>
</div>

<?php include("footer.php"); ?>
