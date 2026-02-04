<?php
/**
 * Site Settings Management
 * Manage Hero, About, Resume, and Contact sections
 */
$pageTitle = 'Site Settings';
// Include auth first to ensure authentication before any processing
require_once 'auth.php';
Auth::requireLogin();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/image_helper.php';

$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? null)) {
        $message = 'Session expired or invalid request. Please reload and try again.';
        $messageType = 'error';
    } else {
        $settings = [];
        
        // Hero Section - English
        if (isset($_POST['hero_greeting'])) {
            $settings['hero_greeting'] = $_POST['hero_greeting'];
            $settings['hero_name'] = $_POST['hero_name'];
            $settings['hero_title'] = $_POST['hero_title'];
            $settings['hero_description'] = $_POST['hero_description'];
            // Kurdish
            $settings['hero_greeting_ku'] = $_POST['hero_greeting_ku'] ?? '';
            $settings['hero_title_ku'] = $_POST['hero_title_ku'] ?? '';
            $settings['hero_description_ku'] = $_POST['hero_description_ku'] ?? '';
        }
        
        // Handle hero image upload
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadDir = '../uploads/';
                $newFilename = ImageHelper::processUpload($_FILES['hero_image'], $uploadDir, 'hero_');
                $settings['hero_image'] = 'uploads/' . $newFilename;
            } catch (Exception $e) {
                // Silently fail for now, or log error
            }
        }
        
        // About Section - English
        if (isset($_POST['about_title'])) {
            $settings['about_title'] = $_POST['about_title'];
            $settings['about_paragraph1'] = $_POST['about_paragraph1'];
            $settings['about_paragraph2'] = $_POST['about_paragraph2'];
            $settings['about_experience'] = $_POST['about_experience'];
            $settings['about_projects'] = $_POST['about_projects'];
            $settings['about_clients'] = $_POST['about_clients'];
            // Kurdish
            $settings['about_title_ku'] = $_POST['about_title_ku'] ?? '';
            $settings['about_paragraph1_ku'] = $_POST['about_paragraph1_ku'] ?? '';
            $settings['about_paragraph2_ku'] = $_POST['about_paragraph2_ku'] ?? '';
            $settings['about_experience_ku'] = $_POST['about_experience_ku'] ?? '';
            $settings['about_projects_ku'] = $_POST['about_projects_ku'] ?? '';
            $settings['about_clients_ku'] = $_POST['about_clients_ku'] ?? '';
        }
        
        // Resume Section - Experience (JSON with Kurdish support)
        if (isset($_POST['exp_period'])) {
            $experience = [];
            foreach ($_POST['exp_period'] as $i => $period) {
                if (!empty($period) || !empty($_POST['exp_title'][$i])) {
                    $experience[] = [
                        'period' => $period,
                        'title' => $_POST['exp_title'][$i] ?? '',
                        'title_ku' => $_POST['exp_title_ku'][$i] ?? '',
                        'company' => $_POST['exp_company'][$i] ?? '',
                        'company_ku' => $_POST['exp_company_ku'][$i] ?? '',
                        'description' => $_POST['exp_description'][$i] ?? '',
                        'description_ku' => $_POST['exp_description_ku'][$i] ?? ''
                    ];
                }
            }
            $settings['resume_experience'] = json_encode($experience);
        }
        
        // Resume Section - Education (JSON with Kurdish support)
        if (isset($_POST['edu_period'])) {
            $education = [];
            foreach ($_POST['edu_period'] as $i => $period) {
                if (!empty($period) || !empty($_POST['edu_title'][$i])) {
                    $education[] = [
                        'period' => $period,
                        'title' => $_POST['edu_title'][$i] ?? '',
                        'title_ku' => $_POST['edu_title_ku'][$i] ?? '',
                        'institution' => $_POST['edu_institution'][$i] ?? '',
                        'institution_ku' => $_POST['edu_institution_ku'][$i] ?? '',
                        'description' => $_POST['edu_description'][$i] ?? '',
                        'description_ku' => $_POST['edu_description_ku'][$i] ?? ''
                    ];
                }
            }
            $settings['resume_education'] = json_encode($education);
        }
        
        // Handle resume PDF upload
        if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            $fileName = 'resume_' . time() . '.pdf';
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['resume_file']['tmp_name'], $targetPath)) {
                $settings['resume_file'] = 'uploads/' . $fileName;
            }
        }
        
        // Contact Section
        if (isset($_POST['contact_email'])) {
            $settings['contact_email'] = $_POST['contact_email'];
            $settings['contact_location'] = $_POST['contact_location'];
            $settings['contact_phone'] = $_POST['contact_phone'];
            $settings['social_github'] = $_POST['social_github'];
            $settings['social_linkedin'] = $_POST['social_linkedin'];
            $settings['social_twitter'] = $_POST['social_twitter'];
            $settings['social_instagram'] = $_POST['social_instagram'];
        }
        
        if (!empty($settings) && $db->updateSettings($settings)) {
            $message = 'Settings saved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to save settings.';
            $messageType = 'error';
        }
    }
}

// Get current settings
$settings = $db->getAllSettings();

// Parse JSON for experience and education
$experience = json_decode($settings['resume_experience'] ?? '[]', true) ?: [];
$education = json_decode($settings['resume_education'] ?? '[]', true) ?: [];

$activeTab = $_GET['tab'] ?? 'hero';
?>

<?php if ($message): ?>
<div class="mb-6 p-4 rounded-xl <?= $messageType === 'success' ? 'bg-green-500/20 border border-green-500 text-green-400' : 'bg-red-500/20 border border-red-500 text-red-400' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="mb-6 border-b border-gray-800">
    <nav class="flex gap-4">
        <a href="?tab=hero" class="px-4 py-3 font-medium transition-colors border-b-2 <?= $activeTab === 'hero' ? 'text-purple-400 border-purple-500' : 'text-gray-400 border-transparent hover:text-white' ?>">Hero Section</a>
        <a href="?tab=about" class="px-4 py-3 font-medium transition-colors border-b-2 <?= $activeTab === 'about' ? 'text-purple-400 border-purple-500' : 'text-gray-400 border-transparent hover:text-white' ?>">About Me</a>
        <a href="?tab=resume" class="px-4 py-3 font-medium transition-colors border-b-2 <?= $activeTab === 'resume' ? 'text-purple-400 border-purple-500' : 'text-gray-400 border-transparent hover:text-white' ?>">Resume</a>
        <a href="?tab=contact" class="px-4 py-3 font-medium transition-colors border-b-2 <?= $activeTab === 'contact' ? 'text-purple-400 border-purple-500' : 'text-gray-400 border-transparent hover:text-white' ?>">Contact & Social</a>
    </nav>
</div>

<!-- Hero Section Tab -->
<?php if ($activeTab === 'hero'): ?>
<style>
.lang-tabs { display: flex; gap: 0; margin-bottom: 1.5rem; }
.lang-tab { padding: 0.75rem 1.5rem; background: #1f2937; border: 1px solid #374151; cursor: pointer; font-weight: 500; transition: all 0.2s; color: #9ca3af; }
.lang-tab:first-child { border-radius: 0.75rem 0 0 0.75rem; }
.lang-tab:last-child { border-radius: 0 0.75rem 0.75rem 0; }
.lang-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; color: white; }
.lang-tab:not(.active):hover { background: #374151; }
.lang-content { display: none; }
.lang-content.active { display: block; }
</style>

<form method="POST" enctype="multipart/form-data" class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
    <h2 class="text-xl font-bold mb-6">Hero Section</h2>
    
    <!-- Language Tabs -->
    <div class="lang-tabs">
        <div class="lang-tab active" onclick="switchLang('en', this)">🇬🇧 English</div>
        <div class="lang-tab" onclick="switchLang('ku', this)">🇮🇶 کوردی</div>
    </div>
    
    <!-- English Content -->
    <div id="lang-en" class="lang-content active">
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Greeting Text</label>
                <input type="text" name="hero_greeting" value="<?= htmlspecialchars($settings['hero_greeting'] ?? "Hello, I'm") ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Your Name</label>
                <input type="text" name="hero_name" value="<?= htmlspecialchars($settings['hero_name'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
            </div>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">Professional Title</label>
            <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">Hero Description</label>
            <textarea name="hero_description" rows="3"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"><?= htmlspecialchars($settings['hero_description'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Kurdish Content -->
    <div id="lang-ku" class="lang-content" dir="rtl">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">سڵاوکردن</label>
            <input type="text" name="hero_greeting_ku" value="<?= htmlspecialchars($settings['hero_greeting_ku'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                   style="font-family: 'Noto Sans Arabic', sans-serif;" placeholder="سڵاو، من">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">ناونیشانی پیشەیی</label>
            <input type="text" name="hero_title_ku" value="<?= htmlspecialchars($settings['hero_title_ku'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                   style="font-family: 'Noto Sans Arabic', sans-serif;" placeholder="گەشەپێدەری وێب">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">وەسفی سەرەکی</label>
            <textarea name="hero_description_ku" rows="3"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"
                      style="font-family: 'Noto Sans Arabic', sans-serif;"
                      placeholder="وەسفێکی کورت دەربارەی خۆت..."><?= htmlspecialchars($settings['hero_description_ku'] ?? '') ?></textarea>
        </div>
    </div>
    
    <!-- Profile Image (shared) -->
    <div class="mb-6" dir="ltr">
        <label class="block text-sm font-medium text-gray-400 mb-2">Profile Image</label>
        <?php if (!empty($settings['hero_image'])): ?>
        <div class="mb-3">
            <img src="../<?= htmlspecialchars($settings['hero_image']) ?>" alt="Current profile" class="w-24 h-24 rounded-full object-cover">
        </div>
        <?php endif; ?>
        <input type="file" name="hero_image" accept="image/*"
               class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
    </div>
    
    <button type="submit" class="gradient-bg text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity">
        Save Hero Settings
    </button>
</form>

<script>
function switchLang(lang, element) {
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    element.classList.add('active');
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.getElementById('lang-' + lang).classList.add('active');
}
</script>
<?php endif; ?>

<!-- About Me Tab -->
<?php if ($activeTab === 'about'): ?>
<style>
.lang-tabs { display: flex; gap: 0; margin-bottom: 1.5rem; }
.lang-tab { padding: 0.75rem 1.5rem; background: #1f2937; border: 1px solid #374151; cursor: pointer; font-weight: 500; transition: all 0.2s; color: #9ca3af; }
.lang-tab:first-child { border-radius: 0.75rem 0 0 0.75rem; }
.lang-tab:last-child { border-radius: 0 0.75rem 0.75rem 0; }
.lang-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; color: white; }
.lang-tab:not(.active):hover { background: #374151; }
.lang-content { display: none; }
.lang-content.active { display: block; }
</style>

<form method="POST" class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
    <h2 class="text-xl font-bold mb-6">About Me Section</h2>
    
    <!-- Language Tabs -->
    <div class="lang-tabs">
        <div class="lang-tab active" onclick="switchLang('en', this)">🇬🇧 English</div>
        <div class="lang-tab" onclick="switchLang('ku', this)">🇮🇶 کوردی</div>
    </div>
    
    <!-- English Content -->
    <div id="lang-en" class="lang-content active">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">Section Title</label>
            <input type="text" name="about_title" value="<?= htmlspecialchars($settings['about_title'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">First Paragraph</label>
            <textarea name="about_paragraph1" rows="4"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"><?= htmlspecialchars($settings['about_paragraph1'] ?? '') ?></textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">Second Paragraph</label>
            <textarea name="about_paragraph2" rows="4"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"><?= htmlspecialchars($settings['about_paragraph2'] ?? '') ?></textarea>
        </div>
        
        <h3 class="text-lg font-bold mb-4">Statistics</h3>
        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Experience</label>
                <input type="text" name="about_experience" value="<?= htmlspecialchars($settings['about_experience'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       placeholder="5+ Years Experience">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Projects</label>
                <input type="text" name="about_projects" value="<?= htmlspecialchars($settings['about_projects'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       placeholder="50+ Projects Completed">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Clients</label>
                <input type="text" name="about_clients" value="<?= htmlspecialchars($settings['about_clients'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       placeholder="30+ Happy Clients">
            </div>
        </div>
    </div>
    
    <!-- Kurdish Content -->
    <div id="lang-ku" class="lang-content" dir="rtl">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">ناونیشانی بەش</label>
            <input type="text" name="about_title_ku" value="<?= htmlspecialchars($settings['about_title_ku'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                   style="font-family: 'Noto Sans Arabic', sans-serif;">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">پارەگرافی یەکەم</label>
            <textarea name="about_paragraph1_ku" rows="4"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"
                      style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($settings['about_paragraph1_ku'] ?? '') ?></textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-2">پارەگرافی دووەم</label>
            <textarea name="about_paragraph2_ku" rows="4"
                      class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none resize-none"
                      style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($settings['about_paragraph2_ku'] ?? '') ?></textarea>
        </div>
        
        <h3 class="text-lg font-bold mb-4">ئامارەکان</h3>
        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">ئەزموون</label>
                <input type="text" name="about_experience_ku" value="<?= htmlspecialchars($settings['about_experience_ku'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       style="font-family: 'Noto Sans Arabic', sans-serif;"
                       placeholder="+٥ ساڵ ئەزموون">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">پڕۆژەکان</label>
                <input type="text" name="about_projects_ku" value="<?= htmlspecialchars($settings['about_projects_ku'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       style="font-family: 'Noto Sans Arabic', sans-serif;"
                       placeholder="+٥٠ پڕۆژە">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">کڕیارەکان</label>
                <input type="text" name="about_clients_ku" value="<?= htmlspecialchars($settings['about_clients_ku'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none"
                       style="font-family: 'Noto Sans Arabic', sans-serif;"
                       placeholder="+٣٠ کڕیار">
            </div>
        </div>
    </div>
    
    <button type="submit" class="gradient-bg text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity">
        Save About Settings
    </button>
</form>

<script>
function switchLang(lang, element) {
    document.querySelectorAll('.lang-tab').forEach(tab => tab.classList.remove('active'));
    element.classList.add('active');
    document.querySelectorAll('.lang-content').forEach(content => content.classList.remove('active'));
    document.getElementById('lang-' + lang).classList.add('active');
}
</script>
<?php endif; ?>

<!-- Resume Tab -->
<?php if ($activeTab === 'resume'): ?>
<form method="POST" enctype="multipart/form-data" class="space-y-6">
    <!-- Work Experience -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">Work Experience</h2>
            <button type="button" onclick="addExperience()" class="text-purple-400 hover:text-purple-300 text-sm font-medium">+ Add Experience</button>
        </div>
        
        <div id="experience-container" class="space-y-4">
            <?php foreach ($experience as $i => $exp): ?>
            <div class="experience-entry bg-gray-800 rounded-xl p-4">
                <div class="text-xs text-purple-400 font-medium mb-3">🇬🇧 English</div>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="exp_period[]" value="<?= htmlspecialchars($exp['period']) ?>" placeholder="Period (e.g., 2022 - Present)"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                    <input type="text" name="exp_title[]" value="<?= htmlspecialchars($exp['title']) ?>" placeholder="Job Title"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div class="mb-4">
                    <input type="text" name="exp_company[]" value="<?= htmlspecialchars($exp['company']) ?>" placeholder="Company Name"
                           class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div class="mb-4">
                    <textarea name="exp_description[]" rows="2" placeholder="Description"
                              class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none"><?= htmlspecialchars($exp['description']) ?></textarea>
                </div>
                
                <div class="border-t border-gray-700 pt-3 mt-3">
                    <div class="text-xs text-purple-400 font-medium mb-3" dir="rtl">🇮🇶 کوردی</div>
                    <div class="grid md:grid-cols-2 gap-4 mb-4" dir="rtl">
                        <input type="text" name="exp_title_ku[]" value="<?= htmlspecialchars($exp['title_ku'] ?? '') ?>" placeholder="ناونیشانی کار"
                               class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                        <input type="text" name="exp_company_ku[]" value="<?= htmlspecialchars($exp['company_ku'] ?? '') ?>" placeholder="ناوی کۆمپانیا"
                               class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                    </div>
                    <div class="flex gap-4" dir="rtl">
                        <textarea name="exp_description_ku[]" rows="2" placeholder="وەسف"
                                  class="flex-1 px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none" style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($exp['description_ku'] ?? '') ?></textarea>
                        <button type="button" onclick="this.closest('.experience-entry').remove()" class="text-red-400 hover:text-red-300 p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Education -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">Education</h2>
            <button type="button" onclick="addEducation()" class="text-purple-400 hover:text-purple-300 text-sm font-medium">+ Add Education</button>
        </div>
        
        <div id="education-container" class="space-y-4">
            <?php foreach ($education as $i => $edu): ?>
            <div class="education-entry bg-gray-800 rounded-xl p-4">
                <div class="text-xs text-purple-400 font-medium mb-3">🇬🇧 English</div>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="edu_period[]" value="<?= htmlspecialchars($edu['period']) ?>" placeholder="Period (e.g., 2014 - 2018)"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                    <input type="text" name="edu_title[]" value="<?= htmlspecialchars($edu['title']) ?>" placeholder="Degree/Certificate"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div class="mb-4">
                    <input type="text" name="edu_institution[]" value="<?= htmlspecialchars($edu['institution']) ?>" placeholder="Institution Name"
                           class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div class="mb-4">
                    <textarea name="edu_description[]" rows="2" placeholder="Description"
                              class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none"><?= htmlspecialchars($edu['description']) ?></textarea>
                </div>
                
                <div class="border-t border-gray-700 pt-3 mt-3">
                    <div class="text-xs text-purple-400 font-medium mb-3" dir="rtl">🇮🇶 کوردی</div>
                    <div class="grid md:grid-cols-2 gap-4 mb-4" dir="rtl">
                        <input type="text" name="edu_title_ku[]" value="<?= htmlspecialchars($edu['title_ku'] ?? '') ?>" placeholder="بڕوانامە/سەرتیفیکەیت"
                               class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                        <input type="text" name="edu_institution_ku[]" value="<?= htmlspecialchars($edu['institution_ku'] ?? '') ?>" placeholder="ناوی دامەزراوە"
                               class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                    </div>
                    <div class="flex gap-4" dir="rtl">
                        <textarea name="edu_description_ku[]" rows="2" placeholder="وەسف"
                                  class="flex-1 px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none" style="font-family: 'Noto Sans Arabic', sans-serif;"><?= htmlspecialchars($edu['description_ku'] ?? '') ?></textarea>
                        <button type="button" onclick="this.closest('.education-entry').remove()" class="text-red-400 hover:text-red-300 p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Resume PDF -->
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <h2 class="text-xl font-bold mb-6">Resume PDF</h2>
        <?php if (!empty($settings['resume_file'])): ?>
        <div class="mb-4 p-3 bg-gray-800 rounded-lg flex items-center gap-3">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span class="text-gray-400"><?= htmlspecialchars(basename($settings['resume_file'])) ?></span>
            <a href="../<?= htmlspecialchars($settings['resume_file']) ?>" target="_blank" class="ml-auto text-purple-400 hover:text-purple-300 text-sm">View</a>
        </div>
        <?php endif; ?>
        <input type="file" name="resume_file" accept=".pdf"
               class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
    </div>
    
    <button type="submit" class="gradient-bg text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity">
        Save Resume Settings
    </button>
</form>

<script>
function addExperience() {
    const container = document.getElementById('experience-container');
    const html = `
        <div class="experience-entry bg-gray-800 rounded-xl p-4">
            <div class="text-xs text-purple-400 font-medium mb-3">🇬🇧 English</div>
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <input type="text" name="exp_period[]" placeholder="Period (e.g., 2022 - Present)"
                       class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                <input type="text" name="exp_title[]" placeholder="Job Title"
                       class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
            </div>
            <div class="mb-4">
                <input type="text" name="exp_company[]" placeholder="Company Name"
                       class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
            </div>
            <div class="mb-4">
                <textarea name="exp_description[]" rows="2" placeholder="Description"
                          class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none"></textarea>
            </div>
            
            <div class="border-t border-gray-700 pt-3 mt-3">
                <div class="text-xs text-purple-400 font-medium mb-3" dir="rtl">🇮🇶 کوردی</div>
                <div class="grid md:grid-cols-2 gap-4 mb-4" dir="rtl">
                    <input type="text" name="exp_title_ku[]" placeholder="ناونیشانی کار"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                    <input type="text" name="exp_company_ku[]" placeholder="ناوی کۆمپانیا"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                </div>
                <div class="flex gap-4" dir="rtl">
                    <textarea name="exp_description_ku[]" rows="2" placeholder="وەسف"
                              class="flex-1 px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none" style="font-family: 'Noto Sans Arabic', sans-serif;"></textarea>
                    <button type="button" onclick="this.closest('.experience-entry').remove()" class="text-red-400 hover:text-red-300 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function addEducation() {
    const container = document.getElementById('education-container');
    const html = `
        <div class="education-entry bg-gray-800 rounded-xl p-4">
            <div class="text-xs text-purple-400 font-medium mb-3">🇬🇧 English</div>
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <input type="text" name="edu_period[]" placeholder="Period (e.g., 2014 - 2018)"
                       class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
                <input type="text" name="edu_title[]" placeholder="Degree/Certificate"
                       class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
            </div>
            <div class="mb-4">
                <input type="text" name="edu_institution[]" placeholder="Institution Name"
                       class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none">
            </div>
            <div class="mb-4">
                <textarea name="edu_description[]" rows="2" placeholder="Description"
                          class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none"></textarea>
            </div>
            
            <div class="border-t border-gray-700 pt-3 mt-3">
                <div class="text-xs text-purple-400 font-medium mb-3" dir="rtl">🇮🇶 کوردی</div>
                <div class="grid md:grid-cols-2 gap-4 mb-4" dir="rtl">
                    <input type="text" name="edu_title_ku[]" placeholder="بڕوانامە/سەرتیفیکەیت"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                    <input type="text" name="edu_institution_ku[]" placeholder="ناوی دامەزراوە"
                           class="px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none" style="font-family: 'Noto Sans Arabic', sans-serif;">
                </div>
                <div class="flex gap-4" dir="rtl">
                    <textarea name="edu_description_ku[]" rows="2" placeholder="وەسف"
                              class="flex-1 px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-purple-500 outline-none resize-none" style="font-family: 'Noto Sans Arabic', sans-serif;"></textarea>
                    <button type="button" onclick="this.closest('.education-entry').remove()" class="text-red-400 hover:text-red-300 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}
</script>
<?php endif; ?>

<!-- Contact & Social Tab -->
<?php if ($activeTab === 'contact'): ?>
<form method="POST" class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
    <h2 class="text-xl font-bold mb-6">Contact Information</h2>
    
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Location</label>
            <input type="text" name="contact_location" value="<?= htmlspecialchars($settings['contact_location'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Phone</label>
            <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none">
        </div>
    </div>
    
    <h2 class="text-xl font-bold mb-6">Social Links</h2>
    
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">GitHub URL</label>
            <input type="url" name="social_github" value="<?= htmlspecialchars($settings['social_github'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none" placeholder="https://github.com/username">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">LinkedIn URL</label>
            <input type="url" name="social_linkedin" value="<?= htmlspecialchars($settings['social_linkedin'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none" placeholder="https://linkedin.com/in/username">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Twitter URL</label>
            <input type="url" name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none" placeholder="https://twitter.com/username">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Instagram URL</label>
            <input type="url" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl bg-gray-800 border border-gray-700 focus:border-purple-500 outline-none" placeholder="https://instagram.com/username">
        </div>
    </div>
    
    <button type="submit" class="gradient-bg text-white px-6 py-3 rounded-xl font-medium hover:opacity-90 transition-opacity">
        Save Contact Settings
    </button>
</form>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
