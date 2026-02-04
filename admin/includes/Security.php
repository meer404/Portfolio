<?php
/**
 * Security Helper Class
 * Handles security headers and common security tasks
 */

class Security {
    /**
     * Send security headers to the browser
     */
    public static function headers(): void {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        // Control referrer information
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Basic XSS protection for older browsers
        header('X-XSS-Protection: 1; mode=block');
        
        // HSTS (Strict-Transport-Security) - Verify if HTTPS is active before enabling to avoid lockout if on HTTP
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Sanitize output for HTML context
     */
    public static function e(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
