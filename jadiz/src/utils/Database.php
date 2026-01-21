<?php
/**
 * Database Connection Manager
 * Singleton pattern for database connection
 */

class Database
{
    private static $instance = null;
    private $connection;
    private $host;
    private $user;
    private $pass;
    private $name;

    private function __construct($host, $user, $pass, $name)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance($host = null, $user = null, $pass = null, $name = null)
    {
        if (self::$instance === null) {
            if ($host === null || $user === null || $pass === null || $name === null) {
                throw new Exception('Database credentials required for first initialization');
            }
            self::$instance = new self($host, $user, $pass, $name);
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     */
    private function connect()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->name);

        if ($this->connection->connect_error) {
            throw new Exception('Database connection failed: ' . $this->connection->connect_error);
        }

        // Set charset
        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Get raw mysqli connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute prepared statement
     */
    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    /**
     * Execute query and return result
     */
    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->connection->insert_id;
    }

    /**
     * Get affected rows
     */
    public function affectedRows()
    {
        return $this->connection->affected_rows;
    }

    /**
     * Escape string
     */
    public function escape($str)
    {
        return $this->connection->real_escape_string($str);
    }

    /**
     * Close connection
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->connection->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent serialization
     */
    public function __sleep()
    {
        throw new Exception('Database connection cannot be serialized');
    }
}
?>
