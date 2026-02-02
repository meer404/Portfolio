<?php
/**
 * Client Add/Edit Form with Image Upload
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Client' : 'Add Client';
$client = null;
$errors = [];

// Include auth for database access and authentication before any output
require_once 'auth.php';
Auth::requireLogin();

$db = Database::getInstance()->getConnection();

// Define upload directory
$uploadDir = '../uploads/clients/';

// Load client for editing
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $client = $stmt->fetch();
        if (!$client) {
            header('Location: clients.php');
            exit;
        }
    } catch (Exception $e) {
        header('Location: clients.php');
        exit;
    }
}

// Handle form submission BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $testimonial = trim($_POST['testimonial'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $logo_url = $client['logo_url'] ?? ''; // Keep existing image if no new upload

    if (empty($name)) $errors[] = 'Name is required';
    if (empty($testimonial)) $errors[] = 'Testimonial is required';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5';

    // Handle image upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
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
            $newFilename = uniqid('client_') . '.' . strtolower($extension);
            $uploadPath = $uploadDir . $newFilename;

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if exists and is a local file
                if ($isEdit && !empty($client['logo_url']) && strpos($client['logo_url'], 'uploads/clients/') !== false) {
                    $oldImagePath = '../' . $client['logo_url'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $logo_url = 'uploads/clients/' . $newFilename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE clients SET name = ?, company = ?, logo_url = ?, testimonial = ?, rating = ?, is_featured = ? WHERE id = ?");
                $stmt->execute([$name, $company, $logo_url, $testimonial, $rating, $is_featured, $_GET['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO clients (name, company, logo_url, testimonial, rating, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $company, $logo_url, $testimonial, $rating, $is_featured]);
            }
            header('Location: clients.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to save client';
        }
    }
}

// Now include header and sidebar AFTER all redirect logic
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="max-w-2xl">
    <a href="clients.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Clients
    </a>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
        <?= implode('<br>', $errors) ?>
    </div>
    <?php endif; ?>

    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Client Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="John Doe"
                           value="<?= htmlspecialchars($client['name'] ?? $_POST['name'] ?? '') ?>">
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-300 mb-2">Company</label>
                    <input type="text" id="company" name="company"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="Tech Company Inc."
                           value="<?= htmlspecialchars($client['company'] ?? $_POST['company'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label for="testimonial" class="block text-sm font-medium text-gray-300 mb-2">Testimonial *</label>
                <textarea id="testimonial" name="testimonial" rows="4" required
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                          placeholder="Write the client's testimonial here..."><?= htmlspecialchars($client['testimonial'] ?? $_POST['testimonial'] ?? '') ?></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-300 mb-2">Rating *</label>
                    <select id="rating" name="rating" required
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= ($client['rating'] ?? $_POST['rating'] ?? 5) == $i ? 'selected' : '' ?>>
                            <?= $i ?> Star<?= $i > 1 ? 's' : '' ?> <?= str_repeat('⭐', $i) ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Featured</label>
                    <label class="flex items-center gap-3 px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl cursor-pointer hover:border-purple-500 transition-all">
                        <input type="checkbox" name="is_featured" value="1" 
                               <?= ($client['is_featured'] ?? $_POST['is_featured'] ?? 0) ? 'checked' : '' ?>
                               class="w-5 h-5 rounded bg-gray-700 border-gray-600 text-purple-500 focus:ring-purple-500">
                        <span class="text-gray-300">Mark as featured</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Client Photo</label>
                
                <?php if ($isEdit && !empty($client['logo_url'])): ?>
                <div class="mb-4 p-4 bg-gray-800 rounded-xl">
                    <p class="text-sm text-gray-400 mb-2">Current Photo:</p>
                    <?php 
                    $currentImgSrc = (strpos($client['logo_url'], 'http') === 0) ? $client['logo_url'] : '../' . $client['logo_url'];
                    ?>
                    <img src="<?= htmlspecialchars($currentImgSrc) ?>" 
                         alt="Current client photo"
                         class="w-20 h-20 object-cover rounded-full">
                </div>
                <?php endif; ?>

                <div class="relative">
                    <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="hidden"
                           onchange="previewImage(this)">
                    <label for="logo" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-700 rounded-xl cursor-pointer hover:border-purple-500 hover:bg-gray-800/50 transition-all">
                        <div id="upload-placeholder" class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400 text-sm">Click to upload photo</p>
                            <p class="text-gray-500 text-xs mt-1">JPG, PNG, GIF, WebP (max 5MB)</p>
                        </div>
                        <img id="image-preview" class="hidden w-40 h-40 object-cover rounded-full" alt="Preview">
                    </label>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 gradient-bg text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all">
                    <?= $isEdit ? 'Update Client' : 'Add Client' ?>
                </button>
                <a href="clients.php" class="px-6 py-3 bg-gray-700 text-white rounded-xl font-semibold hover:bg-gray-600 transition-all">
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
