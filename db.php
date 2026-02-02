<?php
/**
 * Database Connection Class
 * Uses PDO for secure database operations with prepared statements
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    
    private string $host = 'localhost';
    private string $dbname = 'portfolio_db';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     */
    private function connect(): void {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }

    /**
     * Get PDO connection instance
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Fetch all projects from database
     */
    public function getProjects(?int $limit = null): array {
        try {
            $sql = "SELECT * FROM projects ORDER BY created_at DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching projects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch a single project by ID
     */
    public function getProjectById(int $id): ?array {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching project: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch all blog posts from database
     */
    public function getBlogs(?int $limit = null): array {
        try {
            $sql = "SELECT * FROM blogs ORDER BY created_at DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching blogs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save contact message to database
     */
    public function saveMessage(string $name, string $email, string $subject, string $message): bool {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO messages (sender_name, sender_email, subject, message_text) VALUES (?, ?, ?, ?)"
            );
            return $stmt->execute([$name, $email, $subject, $message]);
        } catch (PDOException $e) {
            error_log("Error saving message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
