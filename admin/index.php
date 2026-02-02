<?php
/**
 * Admin Dashboard
 */
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Get statistics
$projectCount = 0;
$blogCount = 0;
$messageCount = 0;
$unreadCount = 0;
$recentMessages = [];
$dbError = null;

try {
    $db = Database::getInstance()->getConnection();
    
    // Get project count
    $stmt = $db->query("SELECT COUNT(*) FROM projects");
    if ($stmt) {
        $projectCount = (int) $stmt->fetchColumn();
    }
    
    // Get blog count
    $stmt = $db->query("SELECT COUNT(*) FROM blogs");
    if ($stmt) {
        $blogCount = (int) $stmt->fetchColumn();
    }
    
    // Get message counts
    $stmt = $db->query("SELECT COUNT(*) FROM messages");
    if ($stmt) {
        $messageCount = (int) $stmt->fetchColumn();
    }
    
    $stmt = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
    if ($stmt) {
        $unreadCount = (int) $stmt->fetchColumn();
    }
    
    // Get recent messages
    $stmt = $db->query("SELECT * FROM messages ORDER BY sent_at DESC LIMIT 5");
    if ($stmt) {
        $recentMessages = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
    error_log("Dashboard DB Error: " . $e->getMessage());
}
?>

<?php if ($dbError): ?>
<!-- Database Error Alert -->
<div class="bg-red-500/20 border border-red-500 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-red-400 font-medium">Database Error</p>
            <p class="text-red-300 text-sm"><?= htmlspecialchars($dbError) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <span class="text-green-400 text-sm font-medium">Active</span>
        </div>
        <h3 class="text-3xl font-bold mb-1"><?= $projectCount ?></h3>
        <p class="text-gray-400 text-sm">Total Projects</p>
    </div>

    <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </div>
            <span class="text-green-400 text-sm font-medium">Published</span>
        </div>
        <h3 class="text-3xl font-bold mb-1"><?= $blogCount ?></h3>
        <p class="text-gray-400 text-sm">Blog Posts</p>
    </div>

    <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <?php if ($unreadCount > 0): ?>
            <span class="text-yellow-400 text-sm font-medium"><?= $unreadCount ?> New</span>
            <?php endif; ?>
        </div>
        <h3 class="text-3xl font-bold mb-1"><?= $messageCount ?></h3>
        <p class="text-gray-400 text-sm">Messages</p>
    </div>

    <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-pink-500/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
        </div>
        <h3 class="text-3xl font-bold mb-1">Live</h3>
        <p class="text-gray-400 text-sm">Website Status</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid sm:grid-cols-3 gap-4 mb-8">
    <a href="project_form.php" class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-4 flex items-center gap-3 hover:opacity-90 transition-opacity">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-medium">Add Project</span>
    </a>
    <a href="blog_form.php" class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-4 flex items-center gap-3 hover:opacity-90 transition-opacity">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="font-medium">Add Blog Post</span>
    </a>
    <a href="messages.php" class="bg-gradient-to-r from-green-600 to-teal-600 rounded-xl p-4 flex items-center gap-3 hover:opacity-90 transition-opacity">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span class="font-medium">View Messages</span>
    </a>
</div>

<!-- Recent Messages -->
<div class="bg-gray-900 rounded-2xl border border-gray-800">
    <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h2 class="text-lg font-bold">Recent Messages</h2>
        <a href="messages.php" class="text-purple-400 hover:text-purple-300 text-sm font-medium">View All →</a>
    </div>
    <div class="divide-y divide-gray-800">
        <?php if (!empty($recentMessages)): ?>
            <?php foreach ($recentMessages as $msg): ?>
            <a href="message_view.php?id=<?= $msg['id'] ?>" class="block p-4 hover:bg-gray-800/50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400 font-bold flex-shrink-0">
                        <?= strtoupper(substr($msg['sender_name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium truncate"><?= htmlspecialchars($msg['sender_name']) ?></span>
                            <?php if (!$msg['is_read']): ?>
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-400 text-sm truncate"><?= htmlspecialchars($msg['subject']) ?></p>
                        <p class="text-gray-500 text-xs mt-1"><?= date('M d, Y h:i A', strtotime($msg['sent_at'])) ?></p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500">
                <p>No messages yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
