<?php
/**
 * Client Add/Edit Form with Multi-Language Support
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Client' : 'Add Client';
$client = null;
$errors = [];

// Include auth for database access and authentication before any output
require_once 'auth.php';
Auth::requireLogin();
require_once 'includes/image_helper.php';

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
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Session expired or invalid request. Please reload and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $company_ku = trim($_POST['company_ku'] ?? '');
        $website_url = trim($_POST['website_url'] ?? '');
        $logo_url = $client['logo_url'] ?? '';

        if (empty($name)) $errors[] = 'Name is required';

        // Handle image upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            try {
                $newFilename = ImageHelper::processUpload($_FILES['logo'], $uploadDir, 'client_');

                if ($isEdit && !empty($client['logo_url']) && strpos($client['logo_url'], 'uploads/clients/') !== false) {
                    $oldImagePath = '../' . $client['logo_url'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $logo_url = 'uploads/clients/' . $newFilename;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE clients SET name = ?, company = ?, company_ku = ?, website_url = ?, logo_url = ? WHERE id = ?");
                    $stmt->execute([$name, $company, $company_ku, $website_url, $logo_url, $_GET['id']]);
                } else {
                    $stmt = $db->prepare("INSERT INTO clients (name, company, company_ku, website_url, logo_url) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $company, $company_ku, $website_url, $logo_url]);
                }
                header('Location: clients.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Failed to save client';
            }
        }
    }
}

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
            <?php CSRF::renderInput(); ?>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Client Name *</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       placeholder="John Doe"
                       value="<?= htmlspecialchars($client['name'] ?? $_POST['name'] ?? '') ?>">
            </div>

            <!-- Company Names - English & Kurdish -->
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-300 mb-2">🇬🇧 Company (English)</label>
                    <input type="text" id="company" name="company"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="Tech Company Inc."
                           value="<?= htmlspecialchars($client['company'] ?? $_POST['company'] ?? '') ?>">
                </div>

                <div dir="rtl">
                    <label for="company_ku" class="block text-sm font-medium text-gray-300 mb-2">🇮🇶 کۆمپانیا (کوردی)</label>
                    <input type="text" id="company_ku" name="company_ku"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           style="font-family: 'Noto Sans Arabic', sans-serif;"
                           placeholder="ناوی کۆمپانیا"
                           value="<?= htmlspecialchars($client['company_ku'] ?? $_POST['company_ku'] ?? '') ?>">
                </div>
            </div>

            <div>
                <label for="website_url" class="block text-sm font-medium text-gray-300 mb-2">Website URL</label>
                <input type="url" id="website_url" name="website_url"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                       placeholder="https://example.com"
                       value="<?= htmlspecialchars($client['website_url'] ?? $_POST['website_url'] ?? '') ?>">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Client Logo / Photo</label>
                
                <?php if ($isEdit && !empty($client['logo_url'])): ?>
                <div class="mb-4 p-4 bg-gray-800 rounded-xl">
                    <p class="text-sm text-gray-400 mb-2">Current Image:</p>
                    <?php 
                    $currentImgSrc = (strpos($client['logo_url'], 'http') === 0) ? $client['logo_url'] : '../' . $client['logo_url'];
                    ?>
                    <img src="<?= htmlspecialchars($currentImgSrc) ?>" 
                         alt="Current client"
                         class="w-20 h-20 object-cover rounded-full">
                </div>
                <?php endif; ?>

                <div class="relative">
                    <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="hidden"
                           onchange="previewImage(this)">
                    <label for="logo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-700 rounded-xl cursor-pointer hover:border-purple-500 hover:bg-gray-800/50 transition-all">
                        <div id="upload-placeholder" class="flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400 text-sm">Click to upload</p>
                        </div>
                        <img id="image-preview" class="hidden w-24 h-24 object-cover rounded-full" alt="Preview">
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
