<?php
include("includes/auth_check.php");
include("db_connect.php");
include("header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-success fw-bold">Inventory</h1>
        <p class="text-muted mb-0">Manage cleanup equipment and supplies inventory</p>
    </div>
    <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
        Add New Item
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-success">Inventory Items</h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, item_name, category, quantity, unit, status, date_added 
                            FROM inventory 
                            ORDER BY id DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_badge = $row['status'] == 'available' ? 
                                '<span class="badge bg-success">Available</span>' : 
                                '<span class="badge bg-warning">Low Stock</span>';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= $status_badge ?></td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars(date("M d, Y", strtotime($row['date_added']))) ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editItemModal" 
                                            onclick="editItem(<?= $row['id'] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?= $row['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No inventory items found.
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm" method="POST" action="process_inventory.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name</label>
                        <input type="text" name="item_name" class="form-control" placeholder="e.g. Brooms" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Safety Gear">Safety Gear</option>
                            <option value="Tools">Tools</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity</label>
                        <input type="number" name="quantity" class="form-control" placeholder="Enter quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit</label>
                        <select name="unit" class="form-control" required>
                            <option value="">Select Unit</option>
                            <option value="pcs">Pieces</option>
                            <option value="box">Box</option>
                            <option value="dozen">Dozen</option>
                            <option value="kg">Kilogram</option>
                            <option value="liter">Liter</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="low_stock">Low Stock</option>
                        </select>
                    </div>

                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-success w-100">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm" method="POST" action="process_inventory.php">
                    <input type="hidden" id="editItemId" name="item_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name</label>
                        <input type="text" id="editItemName" name="item_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="editCategory" name="category" class="form-control" required>
                            <option value="Equipment">Equipment</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Safety Gear">Safety Gear</option>
                            <option value="Tools">Tools</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity</label>
                        <input type="number" id="editQuantity" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unit</label>
                        <select id="editUnit" name="unit" class="form-control" required>
                            <option value="pcs">Pieces</option>
                            <option value="box">Box</option>
                            <option value="dozen">Dozen</option>
                            <option value="kg">Kilogram</option>
                            <option value="liter">Liter</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="editStatus" name="status" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="low_stock">Low Stock</option>
                        </select>
                    </div>

                    <input type="hidden" name="action" value="edit">
                    <button type="submit" class="btn btn-primary w-100">Update Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
    function editItem(itemId) {
        // Fetch item data via AJAX
        fetch('get_inventory_item.php?id=' + itemId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editItemId').value = data.id;
                document.getElementById('editItemName').value = data.item_name;
                document.getElementById('editCategory').value = data.category;
                document.getElementById('editQuantity').value = data.quantity;
                document.getElementById('editUnit').value = data.unit;
                document.getElementById('editStatus').value = data.status;
            })
            .catch(error => console.error('Error:', error));
    }

    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch('process_inventory.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&item_id=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting item!');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>
