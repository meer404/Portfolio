<?php
/**
 * CSRF Protection Helper
 * Generates and verifies CSRF tokens to prevent Cross-Site Request Forgery
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CSRF {
    /**
     * Generate a CSRF token and store it in the session
     * @return string The generated token
     */
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the CSRF token from a form submission
     * @param string|null $token The token to verify (usually from $_POST)
     * @return bool True if valid, False otherwise
     */
    public static function verifyToken(?string $token): bool {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Render a hidden input field with the CSRF token
     */
    public static function renderInput(): void {
        $token = self::generateToken();
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
