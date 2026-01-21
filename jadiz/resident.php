<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-success fw-bold">Residents</h1>
        <p class="text-muted mb-0">List of registered residents in the system</p>
    </div>
    <a href="form.php" class="btn btn-success">
        Add New Resident
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-success">Resident Records</h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, first_name, last_name, location, contact_number, date_added 
                            FROM residents 
                            ORDER BY id DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['first_name']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars(date("M d, Y", strtotime($row['date_added']))) ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No resident records found.
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
