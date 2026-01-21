<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-success fw-bold">Resident Letters</h1>
        <p class="text-muted mb-0">Letters from residents with their locations</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-success">All Letters</h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>ID</th>
                        <th>From (Name)</th>
                        <th>Subject</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Date Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, resident_name, subject, resident_location, resident_contact, status, date_sent 
                            FROM letters 
                            ORDER BY date_sent DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_badge = $row['status'] == 'unread' ? 
                                '<span class="badge bg-warning">Unread</span>' : 
                                '<span class="badge bg-success">Read</span>';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['resident_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['resident_location']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['resident_contact'] ?? 'N/A') ?></td>
                                <td><?= $status_badge ?></td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars(date("M d, Y H:i", strtotime($row['date_sent']))) ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#letterModal" 
                                            onclick="viewLetter(<?= $row['id'] ?>)">View</button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No letters received yet.
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

<!-- Letter Modal -->
<div class="modal fade" id="letterModal" tabindex="-1" aria-labelledby="letterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="letterModalLabel">Letter Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="letterContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
    function viewLetter(letterId) {
        // Fetch letter details via AJAX
        fetch('get_letter_details.php?id=' + letterId)
            .then(response => response.json())
            .then(data => {
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-success">From:</label>
                            <p class="text-dark">${escapeHtml(data.resident_name)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-success">Email:</label>
                            <p class="text-dark">${escapeHtml(data.resident_email)}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-success">Location:</label>
                            <p class="text-dark">${escapeHtml(data.resident_location)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-success">📞 Contact:</label>
                            <p class="text-dark">${escapeHtml(data.resident_contact || 'N/A')}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold text-success">Date Sent:</label>
                        <p class="text-dark">${new Date(data.date_sent).toLocaleString()}</p>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold text-success">Subject:</label>
                        <p class="text-dark">${escapeHtml(data.subject)}</p>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold text-success">Message:</label>
                        <div class="border p-3 bg-light" style="white-space: pre-wrap; word-wrap: break-word;">
                            ${escapeHtml(data.message)}
                        </div>
                    </div>
                `;

                document.getElementById('letterContent').innerHTML = html;

                // Mark as read
                fetch('mark_letter_read.php?id=' + letterId);
            })
            .catch(error => console.error('Error:', error));
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
