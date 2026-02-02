<?php
/**
 * Blog Add/Edit Form
 */
require_once __DIR__ . '/../db.php';

$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Blog Post' : 'Add Blog Post';
$blog = null;
$errors = [];

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

// Handle form submission - BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = $blog['image_url'] ?? '';

    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Image size must be less than 5MB';
        } else {
            $uploadDir = '../uploads/blogs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('blog_') . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                // Delete old image if editing
                if ($isEdit && !empty($blog['image_url']) && strpos($blog['image_url'], 'uploads/') !== false) {
                    $oldFile = '../' . $blog['image_url'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $image_url = 'uploads/blogs/' . $filename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE blogs SET title = ?, content = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$title, $content, $image_url, $_GET['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO blogs (title, content, image_url) VALUES (?, ?, ?)");
                $stmt->execute([$title, $content, $image_url]);
            }
            header('Location: blogs.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to save blog post';
        }
    }
}

// NOW include header and sidebar - after all redirects are handled
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
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
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       value="<?= htmlspecialchars($blog['title'] ?? $_POST['title'] ?? '') ?>">
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Featured Image</label>
                <?php if (!empty($blog['image_url'])): ?>
                <div class="mb-3">
                    <img src="../<?= htmlspecialchars($blog['image_url']) ?>" 
                         alt="Current image" 
                         class="w-full max-w-md h-48 object-cover rounded-xl">
                    <p class="text-gray-500 text-sm mt-2">Current image. Upload a new one to replace it.</p>
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white file:cursor-pointer hover:file:bg-purple-700 transition-all">
                <p class="text-gray-500 text-sm mt-2">Accepted formats: JPEG, PNG, GIF, WebP. Max size: 5MB</p>
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
