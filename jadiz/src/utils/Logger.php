<?php
/**
 * Logger Class
 * Handles application logging
 */

class Logger
{
    private static $instance = null;
    private $logPath;
    private $logLevel = 'debug';

    private function __construct()
    {
        $config = Config::getInstance();
        $this->logPath = BASE_PATH . '/' . trim($config->get('LOG_PATH', 'logs/'), '/');
        $this->logLevel = $config->get('LOG_LEVEL', 'debug');

        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log debug message
     */
    public function debug($message, $context = [])
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Log info message
     */
    public function info($message, $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning($message, $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public function error($message, $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical($message, $context = [])
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Log security event
     */
    public function security($event, $details = [])
    {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'SECURITY',
            'event' => $event,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'user_id' => $_SESSION['resident_id'] ?? $_SESSION['admin_id'] ?? null,
            'details' => $details
        ];

        $this->writeLog('security', $log_entry);
    }

    /**
     * Log authentication event
     */
    public function authentication($event, $details = [])
    {
        $this->security('AUTH_' . $event, $details);
    }

    /**
     * Log database operation
     */
    public function database($operation, $details = [])
    {
        $this->info("Database $operation", $details);
    }

    /**
     * Main logging method
     */
    private function log($level, $message, $context = [])
    {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'file' => $context['file'] ?? null,
            'line' => $context['line'] ?? null
        ];

        $this->writeLog('application', $log_entry);
    }

    /**
     * Write log to file
     */
    private function writeLog($type, $entry)
    {
        $filename = $this->logPath . '/' . $type . '_' . date('Y-m-d') . '.log';
        
        $log_line = json_encode($entry) . "\n";
        
        file_put_contents($filename, $log_line, FILE_APPEND);
        chmod($filename, 0644);
    }

    /**
     * Get logs by type and date
     */
    public function getLogs($type = 'application', $date = null)
    {
        $date = $date ?? date('Y-m-d');
        $filename = $this->logPath . '/' . $type . '_' . $date . '.log';

        if (!file_exists($filename)) {
            return [];
        }

        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $logs = [];

        foreach ($lines as $line) {
            if (!empty($line)) {
                $logs[] = json_decode($line, true);
            }
        }

        return $logs;
    }

    /**
     * Clear old logs (older than days)
     */
    public function clearOldLogs($days = 30)
    {
        $cutoffTime = time() - ($days * 86400);
        
        $files = glob($this->logPath . '/*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }
}
?>
