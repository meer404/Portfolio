<?php
/**
 * Messages List
 */
$pageTitle = 'Messages';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = 'Message deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete message';
    }
}

// Fetch messages
try {
    $db = Database::getInstance()->getConnection();
    $messages = $db->query("SELECT * FROM messages ORDER BY sent_at DESC")->fetchAll();
} catch (Exception $e) {
    $messages = [];
    $error = 'Failed to load messages';
}
?>

<?php if (isset($success)): ?>
<div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg mb-6">
    <?= $success ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
    <?= $error ?>
</div>
<?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <p class="text-gray-400"><?= count($messages) ?> messages received</p>
</div>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800/50">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">From</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden md:table-cell">Subject</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden sm:table-cell">Date</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Status</th>
                    <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="hover:bg-gray-800/30 transition-colors <?= !$msg['is_read'] ? 'bg-purple-500/5' : '' ?>">
                        <td class="px-6 py-4">
                            <div>
                                <span class="font-medium <?= !$msg['is_read'] ? 'text-white' : '' ?>"><?= htmlspecialchars($msg['sender_name']) ?></span>
                                <p class="text-gray-500 text-sm"><?= htmlspecialchars($msg['sender_email']) ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-gray-400 truncate max-w-xs"><?= htmlspecialchars($msg['subject']) ?></p>
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            <span class="text-gray-400 text-sm"><?= date('M d, Y', strtotime($msg['sent_at'])) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (!$msg['is_read']): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-purple-400 bg-purple-500/20 px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                New
                            </span>
                            <?php else: ?>
                            <span class="text-gray-500 text-xs">Read</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="message_view.php?id=<?= $msg['id'] ?>" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="?delete=<?= $msg['id'] ?>" onclick="return confirm('Are you sure you want to delete this message?')" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <p>No messages yet</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
