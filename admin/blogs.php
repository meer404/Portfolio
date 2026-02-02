<?php
/**
 * Blogs List
 */
$pageTitle = 'Blog Posts';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get the blog to delete its image
        $stmt = $db->prepare("SELECT image_url FROM blogs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $blogToDelete = $stmt->fetch();
        
        // Delete the image file if it exists
        if ($blogToDelete && !empty($blogToDelete['image_url']) && strpos($blogToDelete['image_url'], 'uploads/') !== false) {
            $imagePath = '../' . $blogToDelete['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = 'Blog post deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete blog post';
    }
}

// Fetch blogs
try {
    $db = Database::getInstance()->getConnection();
    $blogs = $db->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $blogs = [];
    $error = 'Failed to load blog posts';
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
    <p class="text-gray-400"><?= count($blogs) ?> blog posts found</p>
    <a href="blog_form.php" class="gradient-bg text-white px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Blog Post
    </a>
</div>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800/50">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Image</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Title</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden md:table-cell">Excerpt</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden sm:table-cell">Date</th>
                    <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (!empty($blogs)): ?>
                    <?php foreach ($blogs as $blog): ?>
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <?php if (!empty($blog['image_url'])): ?>
                            <img src="../<?= htmlspecialchars($blog['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($blog['title']) ?>"
                                 class="w-16 h-12 object-cover rounded-lg">
                            <?php else: ?>
                            <div class="w-16 h-12 bg-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium"><?= htmlspecialchars($blog['title']) ?></span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-gray-400 text-sm truncate max-w-md"><?= htmlspecialchars(substr($blog['content'], 0, 100)) ?>...</p>
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            <span class="text-gray-400 text-sm"><?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="../blog.php?id=<?= $blog['id'] ?>" target="_blank" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-green-400" title="View Post">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="blog_form.php?id=<?= $blog['id'] ?>" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-blue-400" title="Edit Post">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="?delete=<?= $blog['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?')" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-red-400" title="Delete Post">
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
                            <p>No blog posts yet</p>
                            <a href="blog_form.php" class="text-purple-400 hover:text-purple-300 mt-2 inline-block">Add your first post</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
