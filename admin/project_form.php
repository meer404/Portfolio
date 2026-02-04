<?php
/**
 * Project Add/Edit Form with Multi-Language Support
 */
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$pageTitle = $isEdit ? 'Edit Project' : 'Add Project';
$project = null;
$errors = [];

// Include auth for database access and authentication before any output
require_once 'auth.php';
Auth::requireLogin();
require_once 'includes/image_helper.php';

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
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Session expired or invalid request. Please reload and try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $title_ku = trim($_POST['title_ku'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $description_ku = trim($_POST['description_ku'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $content_ku = trim($_POST['content_ku'] ?? '');
        $project_link = trim($_POST['project_link'] ?? '');
        $github_link = trim($_POST['github_link'] ?? '');
        $technologies = trim($_POST['technologies'] ?? '');
        $image_url = $project['image_url'] ?? ''; // Keep existing image if no new upload
    
        if (empty($title)) $errors[] = 'Title (English) is required';
        if (empty($description)) $errors[] = 'Description (English) is required';
    
        // Handle image upload
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $newFilename = ImageHelper::processUpload($_FILES['image'], $uploadDir, 'project_');
                
                // Delete old image if exists and is a local file
                if ($isEdit && !empty($project['image_url']) && strpos($project['image_url'], 'uploads/projects/') !== false) {
                    $oldImagePath = '../' . $project['image_url'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $image_url = 'uploads/projects/' . $newFilename;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    
        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $db->prepare("UPDATE projects SET title = ?, title_ku = ?, description = ?, description_ku = ?, content = ?, content_ku = ?, image_url = ?, project_link = ?, github_link = ?, technologies = ? WHERE id = ?");
                    $stmt->execute([$title, $title_ku, $description, $description_ku, $content, $content_ku, $image_url, $project_link, $github_link, $technologies, $_GET['id']]);
                } else {
                    $stmt = $db->prepare("INSERT INTO projects (title, title_ku, description, description_ku, content, content_ku, image_url, project_link, github_link, technologies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $title_ku, $description, $description_ku, $content, $content_ku, $image_url, $project_link, $github_link, $technologies]);
                }
                header('Location: projects.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Failed to save project';
            }
        }
    }
}

// Now include header and sidebar AFTER all redirect logic
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
            <?php CSRF::renderInput(); ?>
            
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
                           value="<?= htmlspecialchars($project['title'] ?? $_POST['title'] ?? '') ?>">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Short Description (English) *</label>
                    <textarea id="description" name="description" rows="3" required
                              placeholder="Brief summary shown in project cards"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($project['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Full Content (English)</label>
                    <textarea id="content" name="content" rows="8"
                              placeholder="Detailed project write-up, features, challenges, etc."
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($project['content'] ?? $_POST['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Kurdish Content -->
            <div id="lang-ku" class="lang-content space-y-6" dir="rtl">
                <div>
                    <label for="title_ku" class="block text-sm font-medium text-gray-300 mb-2">ناونیشان (کوردی)</label>
                    <input type="text" id="title_ku" name="title_ku"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           style="font-family: 'Noto Sans Arabic', sans-serif;"
                           value="<?= htmlspecialchars($project['title_ku'] ?? $_POST['title_ku'] ?? '') ?>">
                </div>

                <div>
                    <label for="description_ku" class="block text-sm font-medium text-gray-300 mb-2">وەسفی کورت (کوردی)</label>
                    <textarea id="description_ku" name="description_ku" rows="3"
                              placeholder="کورتەی پڕۆژە"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                              style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($project['description_ku'] ?? $_POST['description_ku'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="content_ku" class="block text-sm font-medium text-gray-300 mb-2">ناوەڕۆکی تەواو (کوردی)</label>
                    <textarea id="content_ku" name="content_ku" rows="8"
                              placeholder="شرۆڤەی تەواوی پڕۆژە"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                              style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($project['content_ku'] ?? $_POST['content_ku'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Image Upload (shared) -->
            <div dir="ltr">
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

            <!-- Project Links (shared) -->
            <div dir="ltr" class="space-y-4">
                <div>
                    <label for="project_link" class="block text-sm font-medium text-gray-300 mb-2">Live Project URL</label>
                    <input type="url" id="project_link" name="project_link"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="https://example.com"
                           value="<?= htmlspecialchars($project['project_link'] ?? $_POST['project_link'] ?? '') ?>">
                </div>

                <div>
                    <label for="github_link" class="block text-sm font-medium text-gray-300 mb-2">GitHub Repository</label>
                    <input type="url" id="github_link" name="github_link"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="https://github.com/username/project"
                           value="<?= htmlspecialchars($project['github_link'] ?? $_POST['github_link'] ?? '') ?>">
                </div>

                <div>
                    <label for="technologies" class="block text-sm font-medium text-gray-300 mb-2">Technologies Used</label>
                    <input type="text" id="technologies" name="technologies"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="PHP, MySQL, Tailwind CSS, JavaScript"
                           value="<?= htmlspecialchars($project['technologies'] ?? $_POST['technologies'] ?? '') ?>">
                    <p class="text-gray-500 text-xs mt-1">Comma-separated list of technologies</p>
                </div>
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
                           value="<?= htmlspecialchars($project['title'] ?? $_POST['title'] ?? '') ?>">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Short Description (English) *</label>
                    <textarea id="description" name="description" rows="3" required
                              placeholder="Brief summary shown in project cards"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($project['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Full Content (English)</label>
                    <textarea id="content" name="content" rows="8"
                              placeholder="Detailed project write-up, features, challenges, etc."
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"><?= htmlspecialchars($project['content'] ?? $_POST['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Kurdish Content -->
            <div id="lang-ku" class="lang-content space-y-6" dir="rtl">
                <div>
                    <label for="title_ku" class="block text-sm font-medium text-gray-300 mb-2">ناونیشان (کوردی)</label>
                    <input type="text" id="title_ku" name="title_ku"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           style="font-family: 'Noto Sans Arabic', sans-serif;"
                           value="<?= htmlspecialchars($project['title_ku'] ?? $_POST['title_ku'] ?? '') ?>">
                </div>

                <div>
                    <label for="description_ku" class="block text-sm font-medium text-gray-300 mb-2">وەسفی کورت (کوردی)</label>
                    <textarea id="description_ku" name="description_ku" rows="3"
                              placeholder="کورتەی پڕۆژە"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                              style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($project['description_ku'] ?? $_POST['description_ku'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="content_ku" class="block text-sm font-medium text-gray-300 mb-2">ناوەڕۆکی تەواو (کوردی)</label>
                    <textarea id="content_ku" name="content_ku" rows="8"
                              placeholder="شرۆڤەی تەواوی پڕۆژە"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                              style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($project['content_ku'] ?? $_POST['content_ku'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Image Upload (shared) -->
            <div dir="ltr">
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

            <!-- Project Links (shared) -->
            <div dir="ltr" class="space-y-4">
                <div>
                    <label for="project_link" class="block text-sm font-medium text-gray-300 mb-2">Live Project URL</label>
                    <input type="url" id="project_link" name="project_link"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="https://example.com"
                           value="<?= htmlspecialchars($project['project_link'] ?? $_POST['project_link'] ?? '') ?>">
                </div>

                <div>
                    <label for="github_link" class="block text-sm font-medium text-gray-300 mb-2">GitHub Repository</label>
                    <input type="url" id="github_link" name="github_link"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="https://github.com/username/project"
                           value="<?= htmlspecialchars($project['github_link'] ?? $_POST['github_link'] ?? '') ?>">
                </div>

                <div>
                    <label for="technologies" class="block text-sm font-medium text-gray-300 mb-2">Technologies Used</label>
                    <input type="text" id="technologies" name="technologies"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="PHP, MySQL, Tailwind CSS, JavaScript"
                           value="<?= htmlspecialchars($project['technologies'] ?? $_POST['technologies'] ?? '') ?>">
                    <p class="text-gray-500 text-xs mt-1">Comma-separated list of technologies</p>
                </div>
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
function switchLang(lang) {
    // Update tabs
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Update content
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.getElementById('lang-' + lang).classList.add('active');
}

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
