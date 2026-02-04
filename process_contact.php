<?php
/**
 * Contact Form Handler
 * Processes AJAX form submissions and saves to database
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'lang.php';
require_once 'db.php';
require_once 'admin/includes/CSRF.php';

// Verify CSRF Token
if (!CSRF::verifyToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired session. Please refresh the page.']);
    exit;
}

// Get and sanitize input
$name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$subject = isset($_POST['subject']) ? trim(htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8')) : '';
$message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8')) : '';

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = t('form.name_required');
} elseif (strlen($name) < 2) {
    $errors[] = t('form.name_min');
}

if (empty($email)) {
    $errors[] = t('form.email_required');
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = t('form.email_invalid');
}

if (empty($subject)) {
    $errors[] = t('form.subject_required');
}

if (empty($message)) {
    $errors[] = t('form.message_required');
} elseif (strlen($message) < 10) {
    $errors[] = t('form.message_min');
}

// Return errors if validation failed
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Save to database
try {
    $db = Database::getInstance();
    $result = $db->saveMessage($name, $email, $subject, $message);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => t('form.success')
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => t('form.error')
        ]);
    }
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => t('form.generic_error')
    ]);
}
