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
    public function getProjects(?int $limit = null, bool $orderByViews = false): array {
        try {
            $sql = "SELECT * FROM projects ORDER BY " . ($orderByViews ? "views DESC, " : "") . "created_at DESC";
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
    public function getBlogs(?int $limit = null, bool $orderByViews = false): array {
        try {
            $sql = "SELECT * FROM blogs ORDER BY " . ($orderByViews ? "views DESC, " : "") . "created_at DESC";
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
     * Fetch a single blog post by ID
     */
    public function getBlogById(int $id): ?array {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM blogs WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching blog: " . $e->getMessage());
            return null;
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
     * Get a single site setting by key
     */
    public function getSetting(string $key): ?string {
        try {
            $stmt = $this->connection->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetchColumn();
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log("Error fetching setting: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get multiple site settings by keys
     */
    public function getSettings(array $keys): array {
        try {
            if (empty($keys)) return [];
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $stmt = $this->connection->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ($placeholders)");
            $stmt->execute($keys);
            $results = [];
            while ($row = $stmt->fetch()) {
                $results[$row['setting_key']] = $row['setting_value'];
            }
            return $results;
        } catch (PDOException $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all site settings
     */
    public function getAllSettings(): array {
        try {
            $stmt = $this->connection->query("SELECT setting_key, setting_value FROM site_settings");
            $results = [];
            while ($row = $stmt->fetch()) {
                $results[$row['setting_key']] = $row['setting_value'];
            }
            return $results;
        } catch (PDOException $e) {
            error_log("Error fetching all settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update a single site setting
     */
    public function updateSetting(string $key, string $value): bool {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            return $stmt->execute([$key, $value]);
        } catch (PDOException $e) {
            error_log("Error updating setting: " . $e->getMessage());
            return false;
        }
    }

    public function updateSettings(array $settings): bool {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare(
                "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all clients/testimonials from database
     */
    public function getClients(?int $limit = null, bool $featuredOnly = false): array {
        try {
            $sql = "SELECT * FROM clients";
            if ($featuredOnly) {
                $sql .= " WHERE is_featured = 1";
            }
            $sql .= " ORDER BY created_at DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching clients: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search across projects and blogs
     */
    public function search(string $query, int $limit = 10): array {
        try {
            $results = ['projects' => [], 'blogs' => []];
            $searchTerm = '%' . $query . '%';
            
            // Search projects
            $stmt = $this->connection->prepare(
                "SELECT id, title, title_ku, description, description_ku, image_url, 'project' as type 
                 FROM projects 
                 WHERE title LIKE ? OR title_ku LIKE ? OR description LIKE ? OR description_ku LIKE ?
                 ORDER BY created_at DESC 
                 LIMIT ?"
            );
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
            $results['projects'] = $stmt->fetchAll();
            
            // Search blogs
            $stmt = $this->connection->prepare(
                "SELECT id, title, title_ku, content, content_ku, image_url, 'blog' as type 
                 FROM blogs 
                 WHERE title LIKE ? OR title_ku LIKE ? OR content LIKE ? OR content_ku LIKE ?
                 ORDER BY created_at DESC 
                 LIMIT ?"
            );
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
            $results['blogs'] = $stmt->fetchAll();
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error searching: " . $e->getMessage());
            return ['projects' => [], 'blogs' => []];
        }
    }

    /**
     * Fetch a single client by ID
     */
    public function getClientById(int $id): ?array {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM clients WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching client: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Record a visit
     * @param string $type 'site', 'project', or 'blog'
     * @param int|null $id ID of the project or blog (required if type is not 'site')
     */
    public function recordVisit(string $type, ?int $id = null): bool {
        try {
            if ($type === 'site') {
                $today = date('Y-m-d');
                $stmt = $this->connection->prepare(
                    "INSERT INTO daily_visits (visit_date, visit_count) VALUES (?, 1)
                     ON DUPLICATE KEY UPDATE visit_count = visit_count + 1"
                );
                return $stmt->execute([$today]);
            } elseif ($type === 'project' && $id) {
                $stmt = $this->connection->prepare("UPDATE projects SET views = views + 1 WHERE id = ?");
                return $stmt->execute([$id]);
            } elseif ($type === 'blog' && $id) {
                $stmt = $this->connection->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
                return $stmt->execute([$id]);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error recording visit: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get daily visits for analytics
     */
    public function getDailyVisits(int $days = 7): array {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM daily_visits ORDER BY visit_date DESC LIMIT ?"
            );
            $stmt->bindValue(1, $days, PDO::PARAM_INT);
            $stmt->execute();
            return array_reverse($stmt->fetchAll());
        } catch (PDOException $e) {
            error_log("Error fetching daily visits: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total visits today
     */
    public function getVisitsToday(): int {
        try {
            $today = date('Y-m-d');
            $stmt = $this->connection->prepare("SELECT visit_count FROM daily_visits WHERE visit_date = ?");
            $stmt->execute([$today]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error fetching visits today: " . $e->getMessage());
            return 0;
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
