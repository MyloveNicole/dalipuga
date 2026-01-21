<?php
/**
 * Input Validator Class
 * Validates user input and sanitizes data
 */

class Validator
{
    private $errors = [];
    private $data = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Validate email
     */
    public function email($field, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "$field must be a valid email address");
            return false;
        }
        return true;
    }

    /**
     * Validate required field
     */
    public function required($field, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        if (empty(trim($value ?? ''))) {
            $this->addError($field, "$field is required");
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $length, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        if (strlen($value) < $length) {
            $this->addError($field, "$field must be at least $length characters");
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $length, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        if (strlen($value) > $length) {
            $this->addError($field, "$field must not exceed $length characters");
            return false;
        }
        return true;
    }

    /**
     * Validate password strength
     */
    public function password($field, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        $errors = [];

        if (strlen($value) < 12) {
            $errors[] = "at least 12 characters";
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = "one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = "one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = "one number";
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?]/', $value)) {
            $errors[] = "one special character (!@#$%^&* etc)";
        }

        if (!empty($errors)) {
            $this->addError($field, "$field must contain " . implode(', ', $errors));
            return false;
        }

        return true;
    }

    /**
     * Validate phone number
     */
    public function phone($field, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        // Simple validation for 10-15 digit numbers
        if (!preg_match('/^[0-9\-\+\s]{10,15}$/', $value)) {
            $this->addError($field, "$field must be a valid phone number");
            return false;
        }
        return true;
    }

    /**
     * Validate regex pattern
     */
    public function regex($field, $pattern, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        if (!preg_match($pattern, $value)) {
            $this->addError($field, "$field format is invalid");
            return false;
        }
        return true;
    }

    /**
     * Validate field matches another field
     */
    public function matches($field, $otherField, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value !== $otherValue) {
            $this->addError($field, "$field must match $otherField");
            return false;
        }
        return true;
    }

    /**
     * Validate unique value in database
     */
    public function unique($field, $table, $value = null)
    {
        $value = $value ?? ($this->data[$field] ?? null);
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table WHERE $field = ?");
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                $this->addError($field, "$field is already taken");
                return false;
            }
        } catch (Exception $e) {
            Logger::getInstance()->error("Validation error: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Sanitize string input
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array
     */
    public static function sanitizeArray($array)
    {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }
        return $sanitized;
    }

    /**
     * Get error message
     */
    public function getError($field)
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get all errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if has errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Add error
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
?>
