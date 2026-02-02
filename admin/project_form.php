<?php
/**
 * Project Add/Edit Form with Image Upload
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Project' : 'Add Project';
$project = null;
$errors = [];

// Include auth for database access and authentication before any output
require_once 'auth.php';
Auth::requireLogin();

$db = Database::getInstance()->getConnection();

// Define upload directory
$uploadDir = '../uploads/projects/';

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

// Handle form submission BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $project_link = trim($_POST['project_link'] ?? '');
    $image_url = $project['image_url'] ?? ''; // Keep existing image if no new upload

    if (empty($title)) $errors[] = 'Title is required';
    if (empty($description)) $errors[] = 'Description is required';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WebP';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image size must be less than 5MB';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('project_') . '.' . strtolower($extension);
            $uploadPath = $uploadDir . $newFilename;

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if exists and is a local file
                if ($isEdit && !empty($project['image_url']) && strpos($project['image_url'], 'uploads/projects/') !== false) {
                    $oldImagePath = '../' . $project['image_url'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $image_url = 'uploads/projects/' . $newFilename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    } elseif (!$isEdit && (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE)) {
        // Image is optional, so no error for new projects without image
    }

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

// Now include header and sidebar AFTER all redirect logic
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
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
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
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
                <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Project Image</label>
                
                <?php if ($isEdit && !empty($project['image_url'])): ?>
                <div class="mb-4 p-4 bg-gray-800 rounded-xl">
                    <p class="text-sm text-gray-400 mb-2">Current Image:</p>
                    <?php 
                    $currentImgSrc = (strpos($project['image_url'], 'http') === 0) ? $project['image_url'] : '../' . $project['image_url'];
                    ?>
                    <img src="<?= htmlspecialchars($currentImgSrc) ?>" 
                         alt="Current project image"
                         class="w-40 h-28 object-cover rounded-lg">
                </div>
                <?php endif; ?>

                <div class="relative">
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="hidden"
                           onchange="previewImage(this)">
                    <label for="image" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-700 rounded-xl cursor-pointer hover:border-purple-500 hover:bg-gray-800/50 transition-all">
                        <div id="upload-placeholder" class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400 text-sm">Click to upload image</p>
                            <p class="text-gray-500 text-xs mt-1">JPG, PNG, GIF, WebP (max 5MB)</p>
                        </div>
                        <img id="image-preview" class="hidden w-full h-full object-contain rounded-xl" alt="Preview">
                    </label>
                </div>
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

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('upload-placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
