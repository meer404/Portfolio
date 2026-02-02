<?php
/**
 * Blog Add/Edit Form
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Blog Post' : 'Add Blog Post';
$blog = null;

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$db = Database::getInstance()->getConnection();

// Load blog for editing
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $blog = $stmt->fetch();
        if (!$blog) {
            header('Location: blogs.php');
            exit;
        }
    } catch (Exception $e) {
        header('Location: blogs.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $errors = [];
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE blogs SET title = ?, content = ? WHERE id = ?");
                $stmt->execute([$title, $content, $_GET['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO blogs (title, content) VALUES (?, ?)");
                $stmt->execute([$title, $content]);
            }
            header('Location: blogs.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to save blog post';
        }
    }
}
?>

<div class="max-w-2xl">
    <a href="blogs.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Blog Posts
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
                       value="<?= htmlspecialchars($blog['title'] ?? $_POST['title'] ?? '') ?>">
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Content *</label>
                <textarea id="content" name="content" rows="12" required
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($blog['content'] ?? $_POST['content'] ?? '') ?></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 gradient-bg text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all">
                    <?= $isEdit ? 'Update Post' : 'Publish Post' ?>
                </button>
                <a href="blogs.php" class="px-6 py-3 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-600 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
