<?php
/**
 * View Single Message
 */
$pageTitle = 'View Message';

// Validate ID first - before any output
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: messages.php');
    exit;
}

// Include header first to get database connection
require_once 'includes/header.php';

$db = Database::getInstance()->getConnection();

// Load message - before sidebar to handle redirects
$message = null;
try {
    $stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $message = $stmt->fetch();
    
    if (!$message) {
        header('Location: messages.php');
        exit;
    }

    // Mark as read (use null coalescing to handle missing is_read column)
    if (!($message['is_read'] ?? 1)) {
        $updateStmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $updateStmt->execute([$_GET['id']]);
    }
} catch (Exception $e) {
    header('Location: messages.php');
    exit;
}

// Now include sidebar - this outputs HTML so no more header() calls after this
require_once 'includes/sidebar.php';
?>

<div class="max-w-3xl">
    <a href="messages.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Messages
    </a>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($message['subject']) ?></h2>
                    <div class="flex items-center gap-4 text-sm text-gray-400">
                        <span class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400 font-bold">
                                <?= strtoupper(substr($message['sender_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <span class="text-white font-medium"><?= htmlspecialchars($message['sender_name']) ?></span>
                                <span class="text-gray-500">&lt;<?= htmlspecialchars($message['sender_email']) ?>&gt;</span>
                            </div>
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-gray-500 text-sm"><?= date('M d, Y', strtotime($message['sent_at'])) ?></span>
                    <p class="text-gray-600 text-xs"><?= date('h:i A', strtotime($message['sent_at'])) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Message Body -->
        <div class="p-6">
            <div class="prose prose-invert max-w-none">
                <p class="text-gray-300 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($message['message_text']) ?></p>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="p-6 bg-gray-800/50 border-t border-gray-800 flex items-center justify-between">
            <a href="mailto:<?= htmlspecialchars($message['sender_email']) ?>?subject=Re: <?= htmlspecialchars($message['subject']) ?>" 
               class="gradient-bg text-white px-6 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Reply
            </a>
            <a href="messages.php?delete=<?= $message['id'] ?>" 
               onclick="return confirm('Are you sure you want to delete this message?')"
               class="text-red-400 hover:text-red-300 flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
