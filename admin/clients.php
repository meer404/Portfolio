<?php
/**
 * Clients List
 */
$pageTitle = 'Clients';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get client details to delete image file
        $stmt = $db->prepare("SELECT logo_url FROM clients WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $clientToDelete = $stmt->fetch();
        
        // Delete from database
        $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        
        // Delete uploaded image if exists
        if ($clientToDelete && !empty($clientToDelete['logo_url']) && strpos($clientToDelete['logo_url'], 'uploads/clients/') !== false) {
            $imagePath = '../' . $clientToDelete['logo_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $success = 'Client deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete client';
    }
}

// Fetch clients
try {
    $db = Database::getInstance()->getConnection();
    $clients = $db->query("SELECT * FROM clients ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $clients = [];
    $error = 'Failed to load clients';
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
    <p class="text-gray-400"><?= count($clients) ?> clients found</p>
    <a href="client_form.php" class="gradient-bg text-white px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Client
    </a>
</div>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800/50">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Photo</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Name</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden md:table-cell">Company</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden lg:table-cell">Testimonial</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-400">Rating</th>
                    <th class="text-center px-6 py-4 text-sm font-medium text-gray-400 hidden sm:table-cell">Featured</th>
                    <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (!empty($clients)): ?>
                    <?php foreach ($clients as $client): ?>
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <?php 
                            $imgUrl = $client['logo_url'];
                            if (!empty($imgUrl)) {
                                $imgSrc = (strpos($imgUrl, 'http') === 0) ? $imgUrl : '../' . $imgUrl;
                            } else {
                                $imgSrc = 'https://via.placeholder.com/100?text=No+Image';
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                 alt="<?= htmlspecialchars($client['name']) ?>"
                                 class="w-12 h-12 object-cover rounded-full">
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium"><?= htmlspecialchars($client['name']) ?></span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <span class="text-gray-400"><?= htmlspecialchars($client['company'] ?? '-') ?></span>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <p class="text-gray-400 text-sm truncate max-w-xs"><?= htmlspecialchars(substr($client['testimonial'], 0, 60)) ?>...</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-0.5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-4 h-4 <?= $i <= ($client['rating'] ?? 5) ? 'text-yellow-400' : 'text-gray-600' ?>" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center hidden sm:table-cell">
                            <?php if ($client['is_featured']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                    Featured
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">
                                    No
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="client_form.php?id=<?= $client['id'] ?>" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="?delete=<?= $client['id'] ?>" onclick="return confirm('Are you sure you want to delete this client?')" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-red-400">
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
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <p>No clients yet</p>
                            <a href="client_form.php" class="text-purple-400 hover:text-purple-300 mt-2 inline-block">Add your first client</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
