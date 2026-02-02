<?php
/**
 * Project Add/Edit Form
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Project' : 'Add Project';
$project = null;

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$db = Database::getInstance()->getConnection();

// Load project for editing
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $project = $stmt->fetch();
        if (!$project) {
            header('Location: projects.php');
            exit;
        }
    } catch (Exception $e) {
        header('Location: projects.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $project_link = trim($_POST['project_link'] ?? '');

    $errors = [];
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($description)) $errors[] = 'Description is required';

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, image_url = ?, project_link = ? WHERE id = ?");
                $stmt->execute([$title, $description, $image_url, $project_link, $_GET['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO projects (title, description, image_url, project_link) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $image_url, $project_link]);
            }
            header('Location: projects.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to save project';
        }
    }
}
?>

<div class="max-w-2xl">
    <a href="projects.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Projects
    </a>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
        <?= implode('<br>', $errors) ?>
    </div>
    <?php endif; ?>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <form method="POST" class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       value="<?= htmlspecialchars($project['title'] ?? $_POST['title'] ?? '') ?>">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description *</label>
                <textarea id="description" name="description" rows="4" required
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($project['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="image_url" class="block text-sm font-medium text-gray-300 mb-2">Image URL</label>
                <input type="url" id="image_url" name="image_url"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       placeholder="https://example.com/image.jpg"
                       value="<?= htmlspecialchars($project['image_url'] ?? $_POST['image_url'] ?? '') ?>">
            </div>

            <div>
                <label for="project_link" class="block text-sm font-medium text-gray-300 mb-2">Project Link</label>
                <input type="url" id="project_link" name="project_link"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       placeholder="https://github.com/yourproject"
                       value="<?= htmlspecialchars($project['project_link'] ?? $_POST['project_link'] ?? '') ?>">
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 gradient-bg text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all">
                    <?= $isEdit ? 'Update Project' : 'Add Project' ?>
                </button>
                <a href="projects.php" class="px-6 py-3 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-600 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
