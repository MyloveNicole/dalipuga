<?php
/**
 * Admin Class
 * Handles admin-related operations
 */

class Admin
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Get admin by ID
     */
    public function getById($id)
    {
        $stmt = $this->database->prepare(
            "SELECT Admin_Id, Username, Email, Contact_Number, date_created FROM admin WHERE Admin_Id = ?"
        );
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get admin by username
     */
    public function getByUsername($username)
    {
        $stmt = $this->database->prepare(
            "SELECT Admin_Id, Username, Email, Contact_Number FROM admin WHERE Username = ?"
        );
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Update profile
     */
    public function updateProfile($id, $data)
    {
        try {
            $updates = [];
            $params = [];
            $types = "";

            if (isset($data['Email'])) {
                $updates[] = "Email = ?";
                $params[] = $data['Email'];
                $types .= "s";
            }

            if (isset($data['Contact_Number'])) {
                $updates[] = "Contact_Number = ?";
                $params[] = $data['Contact_Number'];
                $types .= "s";
            }

            if (empty($updates)) {
                return true;
            }

            $params[] = $id;
            $types .= "i";

            $sql = "UPDATE admin SET " . implode(", ", $updates) . " WHERE Admin_Id = ?";
            $stmt = $this->database->prepare($sql);

            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Admin profile updated", ['admin_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error updating admin profile: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change password
     */
    public function changePassword($id, $oldPassword, $newPassword)
    {
        try {
            $validator = new Validator(['password' => $newPassword]);
            $validator->password('password');

            if ($validator->hasErrors()) {
                throw new Exception($validator->getError('password'));
            }

            $stmt = $this->database->prepare("SELECT Password FROM admin WHERE Admin_Id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row) {
                throw new Exception("Admin not found");
            }

            if (!password_verify($oldPassword, $row['Password'])) {
                $this->logger->security('WRONG_PASSWORD_CHANGE_ATTEMPT', ['admin_id' => $id]);
                throw new Exception("Current password is incorrect");
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $this->database->prepare("UPDATE admin SET Password = ? WHERE Admin_Id = ?");
            $stmt->bind_param("si", $hashedPassword, $id);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Admin password changed", ['admin_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error changing admin password: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all admins
     */
    public function getAll()
    {
        $result = $this->database->query(
            "SELECT Admin_Id, Username, Email, Contact_Number, date_created FROM admin ORDER BY Username ASC"
        );
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Total residents
        $result = $this->database->query("SELECT COUNT(*) as total FROM residents");
        $stats['total_residents'] = $result->fetch_assoc()['total'];

        // Total inventory items
        $result = $this->database->query("SELECT COUNT(*) as total FROM inventory");
        $stats['total_inventory'] = $result->fetch_assoc()['total'];

        // Unread letters
        $result = $this->database->query("SELECT COUNT(*) as total FROM letters WHERE status = 'unread'");
        $stats['unread_letters'] = $result->fetch_assoc()['total'];

        // Low stock items
        $result = $this->database->query("SELECT COUNT(*) as total FROM inventory WHERE status = 'low_stock'");
        $stats['low_stock_items'] = $result->fetch_assoc()['total'];

        return $stats;
    }
}
?>
