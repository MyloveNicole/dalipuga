<?php
/**
 * Inventory Class
 * Handles inventory management
 */

class Inventory
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Get all inventory items
     */
    public function getAll($limit = 50, $offset = 0)
    {
        try {
            $stmt = $this->database->prepare(
                "SELECT id, item_name, category, quantity, unit, status, date_added 
                 FROM inventory ORDER BY item_name ASC LIMIT ? OFFSET ?"
            );
            
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            $this->logger->error("Error fetching inventory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get inventory item by ID
     */
    public function getById($id)
    {
        $stmt = $this->database->prepare(
            "SELECT id, item_name, category, quantity, unit, status, date_added 
             FROM inventory WHERE id = ?"
        );
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get inventory by category
     */
    public function getByCategory($category)
    {
        $stmt = $this->database->prepare(
            "SELECT id, item_name, category, quantity, unit, status, date_added 
             FROM inventory WHERE category = ? ORDER BY item_name ASC"
        );
        
        $stmt->bind_param("s", $category);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Add new inventory item
     */
    public function add($data)
    {
        try {
            $validator = new Validator($data);
            $validator->required('item_name');
            $validator->required('category');
            $validator->required('quantity');
            $validator->required('unit');

            if ($validator->hasErrors()) {
                throw new Exception("Validation failed");
            }

            $stmt = $this->database->prepare(
                "INSERT INTO inventory (item_name, category, quantity, unit, status) 
                 VALUES (?, ?, ?, ?, ?)"
            );

            $status = $data['status'] ?? 'available';
            $stmt->bind_param(
                "ssiss",
                $data['item_name'],
                $data['category'],
                $data['quantity'],
                $data['unit'],
                $status
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to add inventory item");
            }

            $this->logger->info("Inventory item added", ['item_name' => $data['item_name']]);
            return $this->database->lastInsertId();
        } catch (Exception $e) {
            $this->logger->error("Error adding inventory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update inventory item
     */
    public function update($id, $data)
    {
        try {
            $updates = [];
            $params = [];
            $types = "";

            if (isset($data['item_name'])) {
                $updates[] = "item_name = ?";
                $params[] = $data['item_name'];
                $types .= "s";
            }

            if (isset($data['quantity'])) {
                $updates[] = "quantity = ?";
                $params[] = $data['quantity'];
                $types .= "i";
            }

            if (isset($data['status'])) {
                $updates[] = "status = ?";
                $params[] = $data['status'];
                $types .= "s";
            }

            if (empty($updates)) {
                return true;
            }

            $params[] = $id;
            $types .= "i";

            $sql = "UPDATE inventory SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->database->prepare($sql);

            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Inventory item updated", ['item_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error updating inventory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete inventory item
     */
    public function delete($id)
    {
        try {
            $stmt = $this->database->prepare("DELETE FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Inventory item deleted", ['item_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error deleting inventory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        $result = $this->database->query("SELECT COUNT(*) as total FROM inventory");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems($limit = 10)
    {
        $stmt = $this->database->prepare(
            "SELECT id, item_name, category, quantity, unit, status 
             FROM inventory WHERE status = 'low_stock' LIMIT ?"
        );
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
