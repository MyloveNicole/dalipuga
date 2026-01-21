<?php
/**
 * Session Security Middleware
 * Handles session security checks and validation
 */

class SessionSecurityMiddleware
{
    /**
     * Initialize session security
     */
    public static function initialize()
    {
        // Verify session hasn't been hijacked
        self::validateSession();

        // Check for session timeout
        self::checkTimeout();

        // Update last activity
        $_SESSION['last_activity'] = time();
    }

    /**
     * Validate session integrity
     */
    private static function validateSession()
    {
        if (!isset($_SESSION['user_id'])) {
            return; // User not logged in, nothing to validate
        }

        // Check user agent
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            Logger::getInstance()->security('SESSION_HIJACKING_DETECTED', [
                'user_id' => $_SESSION['user_id'],
                'original_agent' => $_SESSION['user_agent'],
                'current_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
            self::destroySession();
            die('Session security violation detected. Please login again.');
        }
    }

    /**
     * Check for session timeout
     */
    private static function checkTimeout()
    {
        if (!isset($_SESSION['user_id'])) {
            return; // User not logged in
        }

        $config = Config::getInstance();
        $timeout = (int)$config->get('SESSION_TIMEOUT', 3600);

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            Logger::getInstance()->info("Session timeout", ['user_id' => $_SESSION['user_id']]);
            self::destroySession();
            header("Location: /jadiz/public/auth/resident_login.php?expired=1");
            exit;
        }
    }

    /**
     * Destroy session safely
     */
    private static function destroySession()
    {
        session_destroy();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']),
            'samesite' => 'Strict'
        ]);
    }
}

/**
 * CSRF Protection Middleware
 * Protects against Cross-Site Request Forgery attacks
 */

class CsrfProtectionMiddleware
{
    /**
     * Verify CSRF token
     */
    public static function verify()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Only verify POST requests
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$token || !isset($_SESSION['csrf_token'])) {
            Logger::getInstance()->security('CSRF_TOKEN_MISSING', ['url' => $_SERVER['REQUEST_URI']]);
            http_response_code(403);
            die('CSRF token validation failed');
        }

        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            Logger::getInstance()->security('CSRF_TOKEN_INVALID', [
                'user_id' => $_SESSION['user_id'] ?? null,
                'url' => $_SERVER['REQUEST_URI']
            ]);
            http_response_code(403);
            die('CSRF token validation failed');
        }

        return true;
    }

    /**
     * Get CSRF token for forms
     */
    public static function getTokenField()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['csrf_token'];
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

/**
 * Authentication Middleware
 * Protects routes that require authentication
 */

class AuthenticationMiddleware
{
    /**
     * Require authentication
     */
    public static function require()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /jadiz/public/auth/resident_login.php");
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($role)
    {
        self::require();

        if ($_SESSION['role'] !== $role) {
            http_response_code(403);
            die('Access denied');
        }
    }

    /**
     * Require any of the roles
     */
    public static function requireAnyRole($roles = [])
    {
        self::require();

        if (!in_array($_SESSION['role'], $roles)) {
            http_response_code(403);
            die('Access denied');
        }
    }
}

/**
 * Input Sanitization Middleware
 * Sanitizes all user inputs
 */

class InputSanitizationMiddleware
{
    /**
     * Sanitize all inputs
     */
    public static function sanitizeAll()
    {
        $_GET = self::sanitizeArray($_GET);
        $_POST = self::sanitizeArray($_POST);
        $_COOKIE = self::sanitizeArray($_COOKIE);
        $_REQUEST = self::sanitizeArray($_REQUEST);
    }

    /**
     * Sanitize array recursively
     */
    private static function sanitizeArray($array)
    {
        $sanitized = [];

        foreach ($array as $key => $value) {
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }

        return $sanitized;
    }
}

/**
 * Security Headers Middleware
 * Sets important security headers
 */

class SecurityHeadersMiddleware
{
    /**
     * Set security headers
     */
    public static function setHeaders()
    {
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Enable XSS protection in older browsers
        header('X-XSS-Protection: 1; mode=block');

        // Enforce HTTPS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // Control referrer
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy (adjust as needed)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' https://fonts.googleapis.com");

        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}
?>
