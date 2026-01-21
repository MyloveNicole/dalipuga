<?php
/**
 * Letter Class
 * Handles resident letters to admin
 */

class Letter
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Send new letter
     */
    public function send($data)
    {
        try {
            $validator = new Validator($data);
            $validator->required('subject');
            $validator->required('message');

            if ($validator->hasErrors()) {
                throw new Exception("Validation failed");
            }

            $stmt = $this->database->prepare(
                "INSERT INTO letters (resident_id, subject, message, resident_location, resident_name, resident_email, resident_contact, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $status = 'unread';
            $stmt->bind_param(
                "isssssss",
                $data['resident_id'],
                $data['subject'],
                $data['message'],
                $data['resident_location'] ?? null,
                $data['resident_name'] ?? null,
                $data['resident_email'] ?? null,
                $data['resident_contact'] ?? null,
                $status
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to send letter");
            }

            $this->logger->info("Letter sent", [
                'resident_id' => $data['resident_id'],
                'subject' => $data['subject']
            ]);

            return $this->database->lastInsertId();
        } catch (Exception $e) {
            $this->logger->error("Error sending letter: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get letter by ID
     */
    public function getById($id)
    {
        $stmt = $this->database->prepare(
            "SELECT id, resident_id, subject, message, resident_location, resident_name, 
                    resident_email, resident_contact, status, date_sent 
             FROM letters WHERE id = ?"
        );
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Get all letters (admin)
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $stmt = $this->database->prepare(
            "SELECT id, resident_id, subject, resident_name, resident_email, status, date_sent 
             FROM letters ORDER BY date_sent DESC LIMIT ? OFFSET ?"
        );
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get letters by resident
     */
    public function getByResident($resident_id, $limit = 50, $offset = 0)
    {
        $stmt = $this->database->prepare(
            "SELECT id, subject, status, date_sent 
             FROM letters WHERE resident_id = ? ORDER BY date_sent DESC LIMIT ? OFFSET ?"
        );
        
        $stmt->bind_param("iii", $resident_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Mark letter as read
     */
    public function markAsRead($id)
    {
        try {
            $stmt = $this->database->prepare("UPDATE letters SET status = 'read' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Letter marked as read", ['letter_id' => $id]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error updating letter: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $result = $this->database->query("SELECT COUNT(*) as total FROM letters WHERE status = 'unread'");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>
