<?php
/**
 * Admin Authentication Helper
 * Handles session management, access control, and security
 */

// Secure session parameters
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // Current domain
        'secure' => isset($_SERVER['HTTPS']), // True if HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/CSRF.php';

class Auth {
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Require login - redirect if not authenticated
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Attempt to log in user with Rate Limiting
     */
    public static function login(string $username, string $password): array {
        $db = Database::getInstance()->getConnection();
        $ip = $_SERVER['REMOTE_ADDR'];

        // 1. Check Rate Limit
        if (self::isRateLimited($db, $ip)) {
            return ['success' => false, 'error' => 'Too many login attempts. Please wait 15 minutes.'];
        }

        try {
            $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Login Success
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Clear previous failed attempts
                self::clearLoginAttempts($db, $ip);
                
                return ['success' => true];
            }
            
            // Login Failed
            self::recordLoginAttempt($db, $ip);
            return ['success' => false, 'error' => 'Invalid username or password'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred. Please try again.'];
        }
    }

    /**
     * Check if IP is rate limited (5 attempts in 15 mins)
     */
    private static function isRateLimited(PDO $db, string $ip): bool {
        $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        $count = $stmt->fetchColumn();
        return $count >= 5;
    }

    /**
     * Record a failed login attempt
     */
    private static function recordLoginAttempt(PDO $db, string $ip): void {
        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
        $stmt->execute([$ip]);
    }

    /**
     * Clear login attempts after successful login
     */
    private static function clearLoginAttempts(PDO $db, string $ip): void {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    }

    /**
     * Log out user
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function getUsername(): string {
        return $_SESSION['admin_username'] ?? 'Admin';
    }

    public static function getAdminId(): int {
        return $_SESSION['admin_id'] ?? 0;
    }
}
