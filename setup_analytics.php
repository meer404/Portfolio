<?php
require_once 'db.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents('migration_analytics.sql');
    
    // Split SQL into individual queries because PDO doesn't always handle multiple queries in one go well depending on config
    // But for this simple case, we might need to be careful with DELIMITER or just run them one by one if possible.
    // The provided SQL uses prepared statements which might be tricky in raw generic execution. 
    // Let's simplify the PHP execution slightly by just running the ALTERs directly if possible, or using the file.
    // Actually, the complex IF logic in SQL is good for idempotency. Let's try executing it.
    
    $db->exec($sql);
    echo "Migration executed successfully.";
} catch (Exception $e) {
    echo "Error executing migration: " . $e->getMessage();
}
?>
