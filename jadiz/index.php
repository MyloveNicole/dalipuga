<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>
<div class="d-flex flex-column justify-content-center align-items-center text-center">
    <h1 class="display-4 text-success fw-bold">Waste Management System</h1>
    <p class="lead text-muted">
        Welcome to the Dalipuga Cleanup Tracking Dashboard.
    </p>
</div>

        <div class="row mt-5">
            <div class="col-md-4 mb-3">
                <div class="card p-4 h-100 hover-shadow">
                    <h3>🏡</h3>
                    <h5>Add Resident</h5>
                    <p class="small text-muted">Register a new household.</p>
                    <a href="form.php" class="btn btn-outline-success stretched-link">Go to Form</a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-4 h-100">
                    <h3>👥</h3>
                    <h5>View Residents</h5>
                    <p class="small text-muted">See registered households.</p>
                    <a href="resident.php" class="btn btn-outline-primary stretched-link">View List</a>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-4 h-100">
                    <h3>🛡️</h3>
                    <h5>Admins</h5>
                    <p class="small text-muted">Manage system administrators.</p>
                    <a href="admins.php" class="btn btn-outline-secondary stretched-link">View Admins</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>