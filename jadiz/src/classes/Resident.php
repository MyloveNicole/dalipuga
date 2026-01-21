<?php
/**
 * Resident Class
 * Handles resident-related operations
 */

class Resident
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Register new resident
     */
    public function register($data)
    {
        // Validate input
        $validator = new Validator($data);
        $validator->required('first_name');
        $validator->required('last_name');
        $validator->required('email');
        $validator->required('password');
        $validator->required('location');
        $validator->email('email');
        $validator->password('password');
        $validator->matches('password', 'confirm_password');
        $validator->unique('email', 'residents');

        if ($validator->hasErrors()) {
            throw new Exception(implode(', ', array_merge(...array_values($validator->getErrors()))));
        }

        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->database->prepare(
                "INSERT INTO residents (first_name, last_name, email, contact_number, location, password) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                throw new Exception("Database error: " . $this->database->getConnection()->error);
            }

            $stmt->bind_param(
                "ssssss",
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['contact_number'] ?? null,
                $data['location'],
                $hashedPassword
            );

            if (!$stmt->execute()) {
                throw new Exception("Registration failed: " . $stmt->error);
            }

            $resident_id = $this->database->lastInsertId();

            $this->logger->info("New resident registered", [
                'resident_id' => $resident_id,
                'email' => $data['email']
            ]);

            return $resident_id;
        } catch (Exception $e) {
            $this->logger->error("Registration error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get resident by ID
     */
    public function getById($id)
    {
        $stmt = $this->database->prepare(
            "SELECT id, first_name, last_name, email, contact_number, location, date_added 
             FROM residents WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Update resident profile
     */
    public function updateProfile($id, $data)
    {
        try {
            $updates = [];
            $params = [];
            $types = "";

            if (isset($data['first_name'])) {
                $updates[] = "first_name = ?";
                $params[] = $data['first_name'];
                $types .= "s";
            }

            if (isset($data['last_name'])) {
                $updates[] = "last_name = ?";
                $params[] = $data['last_name'];
                $types .= "s";
            }

            if (isset($data['contact_number'])) {
                $updates[] = "contact_number = ?";
                $params[] = $data['contact_number'];
                $types .= "s";
            }

            if (isset($data['location'])) {
                $updates[] = "location = ?";
                $params[] = $data['location'];
                $types .= "s";
            }

            if (empty($updates)) {
                return true;
            }

            $params[] = $id;
            $types .= "i";

            $sql = "UPDATE residents SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->database->prepare($sql);

            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Resident profile updated", ['resident_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Profile update error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change password
     */
    public function changePassword($id, $oldPassword, $newPassword)
    {
        try {
            // Validate new password
            $validator = new Validator(['password' => $newPassword]);
            $validator->password('password');

            if ($validator->hasErrors()) {
                throw new Exception($validator->getError('password'));
            }

            // Get current password hash
            $stmt = $this->database->prepare("SELECT password FROM residents WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row) {
                throw new Exception("Resident not found");
            }

            // Verify old password
            if (!password_verify($oldPassword, $row['password'])) {
                $this->logger->security('WRONG_PASSWORD_CHANGE_ATTEMPT', ['resident_id' => $id]);
                throw new Exception("Current password is incorrect");
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $stmt = $this->database->prepare("UPDATE residents SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $id);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Resident password changed", ['resident_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Password change error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all residents (admin only)
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $stmt = $this->database->prepare(
            "SELECT id, first_name, last_name, email, contact_number, location, date_added 
             FROM residents LIMIT ? OFFSET ?"
        );

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get total resident count
     */
    public function getTotalCount()
    {
        $result = $this->database->query("SELECT COUNT(*) as total FROM residents");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>
