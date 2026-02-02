<?php
/**
 * Projects List
 */
$pageTitle = 'Projects';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = 'Project deleted successfully';
    } catch (Exception $e) {
        $error = 'Failed to delete project';
    }
}

// Fetch projects
try {
    $db = Database::getInstance()->getConnection();
    $projects = $db->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $error = 'Failed to load projects';
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
    <p class="text-gray-400"><?= count($projects) ?> projects found</p>
    <a href="project_form.php" class="gradient-bg text-white px-4 py-2 rounded-lg font-medium hover:opacity-90 transition-opacity flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Project
    </a>
</div>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800/50">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Image</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Title</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden md:table-cell">Description</th>
                    <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden sm:table-cell">Date</th>
                    <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <img src="<?= htmlspecialchars($project['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($project['title']) ?>"
                                 class="w-16 h-12 object-cover rounded-lg">
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium"><?= htmlspecialchars($project['title']) ?></span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-gray-400 text-sm truncate max-w-xs"><?= htmlspecialchars(substr($project['description'], 0, 80)) ?>...</p>
                        </td>
                        <td class="px-6 py-4 hidden sm:table-cell">
                            <span class="text-gray-400 text-sm"><?= date('M d, Y', strtotime($project['created_at'])) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="project_form.php?id=<?= $project['id'] ?>" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="?delete=<?= $project['id'] ?>" onclick="return confirm('Are you sure you want to delete this project?')" class="p-2 rounded-lg hover:bg-gray-700 transition-colors text-red-400">
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
                            <p>No projects yet</p>
                            <a href="project_form.php" class="text-purple-400 hover:text-purple-300 mt-2 inline-block">Add your first project</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
