<?php
/**
 * Blog Add/Edit Form with Multi-Language Support
 */

// Include auth first to ensure authentication before any redirects
require_once __DIR__ . '/auth.php';
Auth::requireLogin();
require_once 'includes/image_helper.php';

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
    $title_ku = trim($_POST['title_ku'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $content_ku = trim($_POST['content_ku'] ?? '');
    $image_url = $blog['image_url'] ?? '';

    if (empty($title)) $errors[] = 'Title (English) is required';
    if (empty($content)) $errors[] = 'Content (English) is required';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            // Upload directory
            $uploadDir = '../uploads/blogs/';
            $newFilename = ImageHelper::processUpload($_FILES['image'], $uploadDir, 'blog_');
            
            // Delete old image if editing
            if ($isEdit && !empty($blog['image_url']) && strpos($blog['image_url'], 'uploads/') !== false) {
                $oldFile = '../' . $blog['image_url'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $image_url = 'uploads/blogs/' . $newFilename;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE blogs SET title = ?, title_ku = ?, content = ?, content_ku = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$title, $title_ku, $content, $content_ku, $image_url, $_GET['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO blogs (title, title_ku, content, content_ku, image_url) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $title_ku, $content, $content_ku, $image_url]);
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

<style>
.lang-tabs { display: flex; gap: 0; margin-bottom: 1rem; }
.lang-tab { padding: 0.75rem 1.5rem; background: #1f2937; border: 1px solid #374151; cursor: pointer; font-weight: 500; transition: all 0.2s; }
.lang-tab:first-child { border-radius: 0.75rem 0 0 0.75rem; }
.lang-tab:last-child { border-radius: 0 0.75rem 0.75rem 0; }
.lang-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; color: white; }
.lang-tab:not(.active):hover { background: #374151; }
.lang-content { display: none; }
.lang-content.active { display: block; }
</style>

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
            
            <!-- Language Tabs -->
            <div class="lang-tabs">
                <div class="lang-tab active" onclick="switchLang('en')">🇬🇧 English</div>
                <div class="lang-tab" onclick="switchLang('ku')">🇮🇶 کوردی</div>
            </div>

            <!-- English Content -->
            <div id="lang-en" class="lang-content active space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title (English) *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           value="<?= htmlspecialchars($blog['title'] ?? $_POST['title'] ?? '') ?>">
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Content (English) *</label>
                    <textarea id="content" name="content" rows="10" required
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($blog['content'] ?? $_POST['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Kurdish Content -->
            <div id="lang-ku" class="lang-content space-y-6" dir="rtl">
                <div>
                    <label for="title_ku" class="block text-sm font-medium text-gray-300 mb-2">ناونیشان (کوردی)</label>
                    <input type="text" id="title_ku" name="title_ku"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           style="font-family: 'Noto Sans Arabic', sans-serif;"
                           value="<?= htmlspecialchars($blog['title_ku'] ?? $_POST['title_ku'] ?? '') ?>">
                </div>

                <div>
                    <label for="content_ku" class="block text-sm font-medium text-gray-300 mb-2">ناوەڕۆک (کوردی)</label>
                    <textarea id="content_ku" name="content_ku" rows="10"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                              style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($blog['content_ku'] ?? $_POST['content_ku'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Image Upload (shared) -->
            <div dir="ltr">
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

<script>
function switchLang(lang) {
    // Update tabs
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Update content
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.getElementById('lang-' + lang).classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
