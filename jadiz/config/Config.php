<?php
/**
 * Configuration Class
 * Singleton pattern for managing application configuration
 */

class Config
{
    private static $instance = null;
    private $config = [];
    private $envLoaded = false;

    private function __construct()
    {
        $this->loadEnvironment();
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
     * Load environment variables from .env file
     */
    private function loadEnvironment()
    {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Copy .env.example to .env and configure.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, ' "\'');
                
                $this->config[$key] = $value;
                $_ENV[$key] = $value;
            }
        }

        $this->envLoaded = true;
    }

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (supports dot notation: db.host)
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (strpos($key, '.') !== false) {
            return $this->getDotNotation($key, $default);
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Get value using dot notation (e.g., 'db.host')
     */
    private function getDotNotation($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set configuration value
     */
    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Get all configuration
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * Check if key exists
     */
    public function has($key)
    {
        return isset($this->config[$key]);
    }
}
?>
