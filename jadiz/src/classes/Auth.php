<?php
/**
 * Authentication Class
 * Handles user authentication and authorization
 */

class Auth
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Authenticate resident by email and password
     */
    public function authenticateResident($email, $password)
    {
        // Validate input
        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email');
        $validator->required('password');
        $validator->email('email');

        if ($validator->hasErrors()) {
            throw new Exception($validator->getError('email') ?? $validator->getError('password'));
        }

        // Rate limit check
        $this->checkRateLimit('login_' . $email);

        try {
            $stmt = $this->database->prepare("SELECT id, first_name, last_name, email, password FROM residents WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $this->database->getConnection()->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->logger->authentication('FAILED', ['email' => $email, 'reason' => 'user_not_found']);
                throw new Exception("Invalid email or password");
            }

            $row = $result->fetch_assoc();

            // Verify password (only accept hashed passwords)
            if (!password_verify($password, $row['password'])) {
                $this->logger->authentication('FAILED', ['email' => $email, 'reason' => 'invalid_password']);
                throw new Exception("Invalid email or password");
            }

            // Clear rate limit on successful login
            $this->clearRateLimit('login_' . $email);

            $this->logger->authentication('SUCCESS', ['user_id' => $row['id'], 'email' => $email]);

            return [
                'id' => $row['id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'role' => 'resident'
            ];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "Invalid") !== 0) {
                $this->logger->error("Authentication error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Authenticate admin by username and password
     */
    public function authenticateAdmin($username, $password)
    {
        // Validate input
        $validator = new Validator(['username' => $username, 'password' => $password]);
        $validator->required('username');
        $validator->required('password');

        if ($validator->hasErrors()) {
            throw new Exception("Invalid username or password");
        }

        // Rate limit check
        $this->checkRateLimit('admin_login_' . $username);

        try {
            $stmt = $this->database->prepare("SELECT Admin_Id, Username, Password FROM admin WHERE Username = ?");
            if (!$stmt) {
                throw new Exception("Database error");
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->logger->authentication('ADMIN_FAILED', ['username' => $username, 'reason' => 'user_not_found']);
                throw new Exception("Invalid username or password");
            }

            $row = $result->fetch_assoc();

            // Verify password (only accept hashed passwords)
            if (!password_verify($password, $row['Password'])) {
                $this->logger->authentication('ADMIN_FAILED', ['username' => $username, 'reason' => 'invalid_password']);
                throw new Exception("Invalid username or password");
            }

            // Clear rate limit on successful login
            $this->clearRateLimit('admin_login_' . $username);

            $this->logger->authentication('ADMIN_SUCCESS', ['admin_id' => $row['Admin_Id'], 'username' => $username]);

            return [
                'id' => $row['Admin_Id'],
                'username' => $row['Username'],
                'role' => 'admin'
            ];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "Invalid") !== 0) {
                $this->logger->error("Admin authentication error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Create user session
     */
    public function createSession($userData)
    {
        // Regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'] ?? $userData['username'];
        $_SESSION['user_email'] = $userData['email'] ?? null;
        $_SESSION['role'] = $userData['role'];
        $_SESSION['session_created'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->logger->info("Session created", ['user_id' => $userData['id']]);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->isAuthenticated() && $_SESSION['role'] === $role;
    }

    /**
     * Check for session hijacking
     */
    public function validateSession()
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Check if user agent changed
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logger->security('SESSION_HIJACKING_DETECTED', ['user_id' => $_SESSION['user_id']]);
            return false;
        }

        // Check if IP changed (optional - can cause issues with mobile users)
        // if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        //     return false;
        // }

        return true;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token
     */
    public function getCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        
        session_destroy();
        
        if ($user_id) {
            $this->logger->info("User logged out", ['user_id' => $user_id]);
        }
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit($key, $maxAttempts = 5, $timeout = 900)
    {
        $lockFile = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.json';

        if (file_exists($lockFile)) {
            $data = json_decode(file_get_contents($lockFile), true);

            if (time() - $data['reset_time'] > $timeout) {
                unlink($lockFile);
                return;
            }

            if ($data['attempts'] >= $maxAttempts) {
                $this->logger->security('RATE_LIMIT_EXCEEDED', ['key' => $key, 'attempts' => $data['attempts']]);
                throw new Exception("Too many attempts. Please try again later.");
            }

            $data['attempts']++;
        } else {
            $data = ['attempts' => 1, 'reset_time' => time()];
        }

        file_put_contents($lockFile, json_encode($data));
        chmod($lockFile, 0644);
    }

    /**
     * Clear rate limit
     */
    private function clearRateLimit($key)
    {
        $lockFile = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.json';
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }
}
?>
