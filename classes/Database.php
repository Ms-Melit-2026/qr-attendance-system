<?php
/**
 * Database Class for handling database operations
 */

class Database {
    private $conn;
    private $stmt;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Prepare query
     */
    public function prepare($query) {
        $this->stmt = $this->conn->prepare($query);
        if (!$this->stmt) {
            throw new Exception('Prepare failed: ' . $this->conn->error);
        }
        return $this;
    }

    /**
     * Bind parameters
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = MYSQLI_TYPE_LONG;
                    break;
                case is_float($value):
                    $type = MYSQLI_TYPE_DOUBLE;
                    break;
                case is_string($value):
                    $type = MYSQLI_TYPE_STRING;
                    break;
                default:
                    $type = MYSQLI_TYPE_STRING;
            }
        }
        $this->stmt->bind_param($type, $value);
        return $this;
    }

    /**
     * Execute query
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (Exception $e) {
            throw new Exception('Execute failed: ' . $e->getMessage());
        }
    }

    /**
     * Get result as array
     */
    public function getResults() {
        $result = $this->stmt->get_result();
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Get single result
     */
    public function getSingleResult() {
        $result = $this->stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->num_rows;
    }

    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->conn->affected_rows;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }

    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn->close();
    }
}
?>