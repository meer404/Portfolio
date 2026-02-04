<?php
/**
 * Admin Setup Script
 * Run this once to create the default admin account
 * Delete this file after setup for security
 */

require_once __DIR__ . '/db.php';

$username = 'MirMohammed';
$password = 'Mira19$==';
$email = 'meermohammed80@gmail.com';

// Generate proper bcrypt hash
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if admin already exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        // Update existing admin password
        $update = $db->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $update->execute([$hashedPassword, $username]);
        echo "<h2 style='color: green;'>✅ Admin password has been reset!</h2>";
    } else {
        // Insert new admin
        $insert = $db->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $insert->execute([$username, $hashedPassword, $email]);
        echo "<h2 style='color: green;'>✅ Admin account created!</h2>";
    }
    
    echo "<p><strong>Username:</strong> $username</p>";
    echo "<p><strong>Password:</strong> $password</p>";
    echo "<p><a href='admin/login.php'>Go to Login Page</a></p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANT:</strong> Delete this file (setup_admin.php) after logging in for security!</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Make sure you have imported the database.sql file first!</p>";
}
