<?php
/**
 * Admin Authentication Helper
 * Handles session management and access control
 */

session_start();

require_once __DIR__ . '/../db.php';

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
     * Attempt to log in user
     */
    public static function login(string $username, string $password): bool {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
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

    /**
     * Get current admin username
     */
    public static function getUsername(): string {
        return $_SESSION['admin_username'] ?? 'Admin';
    }

    /**
     * Get current admin ID
     */
    public static function getAdminId(): int {
        return $_SESSION['admin_id'] ?? 0;
    }
}
