<?php
require_once 'db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Read the migration file
    $sql = file_get_contents('migration_security.sql');
    
    if ($sql) {
        $db->exec($sql);
        echo "Security migration applied successfully! 'login_attempts' table created.";
    } else {
        echo "Error: Could not read migration_security.sql";
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
