<?php
/**
 * Portfolio Website - Main Page
 * A modern, responsive personal portfolio
 */

require_once 'lang.php';
require_once 'db.php';
require_once 'seo.php';

// Fetch data from database (limited for homepage)
try {
    $db = Database::getInstance();
    $projects = $db->getProjects(6);  // Only show 6 projects
    $blogs = $db->getBlogs(4);        // Only show 4 blogs
    $clients = $db->getClients(5);    // Get client testimonials
    $settings = $db->getAllSettings(); // Get all site settings
    
    // Parse JSON for experience and education
    $experience = json_decode($settings['resume_experience'] ?? '[]', true) ?: [];
    $education = json_decode($settings['resume_education'] ?? '[]', true) ?: [];
} catch (Exception $e) {
    $projects = [];
    $blogs = [];
    $clients = [];
    $settings = [];
    $experience = [];
    $education = [];
}

// Helper function to get setting with default value
function getSetting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

// SEO variables
$seoName = getSetting('hero_name', 'John Doe');
$seoTitle = getSetting('hero_title', 'Full-Stack Developer');
$seoDescription = getLocalizedSetting('hero_description', 'I craft beautiful, responsive, and user-friendly web applications that solve real-world problems and deliver exceptional user experiences.');
$seoImage = getSetting('hero_image', '');
$seoEmail = getSetting('contact_email', '');
$seoPhone = getSetting('contact_phone', '');
$seoLocation = getSetting('contact_location', '');
$seoGithub = getSetting('social_github', '');
$seoLinkedin = getSetting('social_linkedin', '');
$seoTwitter = getSetting('social_twitter', '');
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getDir() ?>" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= renderSeoMeta([
        'title' => $seoName . ' | ' . $seoTitle,
        'description' => $seoDescription,
        'keywords' => getDefaultKeywords('default') . ', ' . $seoName . ', ' . $seoLocation,
        'image' => $seoImage,
        'type' => 'website',
        'author' => $seoName,
    ]) ?>
    <title><?= htmlspecialchars($seoName) ?> | <?= htmlspecialchars($seoTitle) ?></title>
    <link rel="icon" type="image/png" sizes="192x192" href="uploads/logo.png">

    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.6s ease-out forwards',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                },
            },
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isRTL()): ?>
    <style>
        body { font-family: 'Noto Sans Arabic', 'Inter', system-ui, sans-serif; }
    </style>
    <?php endif; ?>
    
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dark .glass-card {
            background: rgba(255, 255, 255, 0.05);
        }
        .light .glass-card {
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
        }
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        .hero-gradient {
            background: radial-gradient(ellipse at top, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at bottom, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans transition-colors duration-300">
    
    <!-- Navigation -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                 <a href="#" class="inline-block">
                        <img src="uploads/logo.png" alt="MIR.CODES" class="h-16 w-auto">
                    </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.home') ?></a>
                    <a href="#about" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.about') ?></a>
                    <a href="#resume" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.resume') ?></a>
                    <a href="#clients" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.clients') ?></a>
                    <a href="#portfolio" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.portfolio') ?></a>
                    <a href="#blog" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.blog') ?></a>
                    <a href="#contact" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.contact') ?></a>
                    
                    <!-- Language Switcher -->
                    <a href="<?= langUrl(getOtherLanguage()) ?>" class="px-3 py-1 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition-colors">
                        <?= t('language.switch') ?>
                    </a>
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 sun-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-5 h-5 moon-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center gap-2">
                    <a href="<?= langUrl(getOtherLanguage()) ?>" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 font-medium">
                        <?= t('language.switch') ?>
                    </a>
                    <button id="theme-toggle-mobile" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="w-5 h-5 sun-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-5 h-5 moon-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                        
                    </button>
                    
                    <button id="mobile-menu-btn" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="#home" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.home') ?></a>
                    <a href="#about" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.about') ?></a>
                    <a href="#resume" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.resume') ?></a>
                    <a href="#clients" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.clients') ?></a>
                    <a href="#portfolio" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.portfolio') ?></a>
                    <a href="#blog" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.blog') ?></a>
                    <a href="#contact" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 py-2 transition-colors"><?= t('nav.contact') ?></a>
                    <a href="<?= langUrl(getOtherLanguage()) ?>" class="text-purple-600 dark:text-purple-400 font-medium py-2"><?= t('language.switch') ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="min-h-screen flex items-center justify-center hero-gradient pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1 text-center md:text-left">
                    <p class="text-purple-600 dark:text-purple-400 font-medium mb-4 animate-fade-in"><?= htmlspecialchars(getLocalizedSetting('hero_greeting', "Hello, I'm")) ?></p>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 animate-slide-up">
                        <span class="gradient-text"><?= htmlspecialchars(getSetting('hero_name', 'John Doe')) ?></span>
                    </h1>
                    <h2 class="text-xl sm:text-2xl text-gray-600 dark:text-gray-400 mb-8 animate-fade-in" style="animation-delay: 0.2s;">
                        <?= htmlspecialchars(getLocalizedSetting('hero_title', 'Full-Stack Web Developer')) ?>
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg mb-10 max-w-lg mx-auto md:mx-0 animate-fade-in" style="animation-delay: 0.3s;">
                        <?= htmlspecialchars(getLocalizedSetting('hero_description', 'I craft beautiful, responsive, and user-friendly web applications that solve real-world problems and deliver exceptional user experiences.')) ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start animate-fade-in" style="animation-delay: 0.4s;">
                        <a href="#contact" class="gradient-bg text-white px-8 py-4 rounded-full font-semibold hover:opacity-90 transform hover:scale-105 transition-all duration-300 shadow-lg shadow-purple-500/25">
                            <?= t('hero.get_in_touch') ?>
                        </a>
                        <a href="#portfolio" class="border-2 border-purple-600 text-purple-600 dark:text-purple-400 px-8 py-4 rounded-full font-semibold hover:bg-purple-600 hover:text-white transition-all duration-300">
                            <?= t('hero.view_my_work') ?>
                        </a>
                    </div>
                </div>
                <div class="order-1 md:order-2 flex justify-center animate-fade-in">
                    <div class="relative">
                        <div class="w-64 h-64 sm:w-80 sm:h-80 rounded-full gradient-bg p-1">
                            <div class="w-full h-full rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($settings['hero_image'])): ?>
                                <img src="<?= htmlspecialchars($settings['hero_image']) ?>" alt="<?= htmlspecialchars(getSetting('hero_name', 'Profile')) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                <svg class="w-32 h-32 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-24 h-24 rounded-full gradient-bg opacity-50 blur-2xl animate-pulse-slow"></div>
                        <div class="absolute -top-4 -left-4 w-20 h-20 rounded-full bg-purple-400 opacity-30 blur-2xl animate-pulse-slow"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('about.title') ?> <span class="gradient-text"><?= t('about.me') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="animate-on-scroll">
                    <h3 class="text-2xl font-bold mb-6"><?= htmlspecialchars(getLocalizedSetting('about_title', 'A Passionate Developer & Problem Solver')) ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        <?= htmlspecialchars(getLocalizedSetting('about_paragraph1', 'With over 5 years of experience in web development, I specialize in creating modern, scalable, and user-centric applications. My journey started with a curiosity about how websites work, and it has evolved into a deep passion for crafting digital experiences.')) ?>
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                        <?= htmlspecialchars(getLocalizedSetting('about_paragraph2', "I believe in writing clean, maintainable code and staying up-to-date with the latest technologies. When I'm not coding, you'll find me exploring new frameworks, contributing to open-source projects, or sharing knowledge through my blog.")) ?>
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full gradient-bg"></div>
                            <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars(getLocalizedSetting('about_experience', '5+ Years Experience')) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full gradient-bg"></div>
                            <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars(getLocalizedSetting('about_projects', '50+ Projects Completed')) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full gradient-bg"></div>
                            <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars(getLocalizedSetting('about_clients', '30+ Happy Clients')) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="animate-on-scroll">
                    <h3 class="text-2xl font-bold mb-6"><?= t('about.my_skills') ?></h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <?php
                        $skills = [
                            ['name' => 'HTML5', 'icon' => '🌐'],
                            ['name' => 'CSS3', 'icon' => '🎨'],
                            ['name' => 'JavaScript', 'icon' => '⚡'],
                            ['name' => 'PHP', 'icon' => '🐘'],
                            ['name' => 'MySQL', 'icon' => '🗄️'],
                            ['name' => 'React', 'icon' => '⚛️'],
                            ['name' => 'Node.js', 'icon' => '🟢'],
                            ['name' => 'Tailwind', 'icon' => '💨'],
                            ['name' => 'Git', 'icon' => '📦'],
                        ];
                        foreach ($skills as $skill):
                        ?>
                        <div class="glass-card rounded-xl p-4 text-center hover:scale-105 transition-transform duration-300">
                            <span class="text-2xl mb-2 block"><?= $skill['icon'] ?></span>
                            <span class="text-sm font-medium"><?= $skill['name'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Resume Section -->
    <section id="resume" class="py-20 bg-gray-50 dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('resume.title') ?> <span class="gradient-text"><?= t('resume.highlight') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Experience -->
                <div class="animate-on-scroll">
                    <h3 class="text-2xl font-bold mb-8 flex items-center gap-3">
                        <span class="gradient-bg text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <?= t('resume.work_experience') ?>
                    </h3>
                    <div class="space-y-8">
                        <?php foreach ($experience as $exp): ?>
                        <?php
                            $lang = getCurrentLanguage();
                            $expTitle = ($lang === 'ku' && !empty($exp['title_ku'])) ? $exp['title_ku'] : ($exp['title'] ?? '');
                            $expCompany = ($lang === 'ku' && !empty($exp['company_ku'])) ? $exp['company_ku'] : ($exp['company'] ?? '');
                            $expDesc = ($lang === 'ku' && !empty($exp['description_ku'])) ? $exp['description_ku'] : ($exp['description'] ?? '');
                        ?>
                        <div class="relative pl-8 border-l-2 border-purple-500/30">
                            <div class="absolute left-0 top-0 w-4 h-4 -translate-x-1/2 rounded-full gradient-bg"></div>
                            <div class="glass-card rounded-xl p-6">
                                <span class="text-purple-600 dark:text-purple-400 text-sm font-medium"><?= htmlspecialchars($exp['period'] ?? '') ?></span>
                                <h4 class="text-xl font-bold mt-2"><?= htmlspecialchars($expTitle) ?></h4>
                                <p class="text-gray-500 dark:text-gray-400 mb-3"><?= htmlspecialchars($expCompany) ?></p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm"><?= htmlspecialchars($expDesc) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($experience)): ?>
                        <p class="text-gray-500"><?= t('resume.no_experience') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Education -->
                <div class="animate-on-scroll">
                    <h3 class="text-2xl font-bold mb-8 flex items-center gap-3">
                        <span class="gradient-bg text-white p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                            </svg>
                        </span>
                        <?= t('resume.education') ?>
                    </h3>
                    <div class="space-y-8">
                        <?php foreach ($education as $edu): ?>
                        <?php
                            $lang = getCurrentLanguage();
                            $eduTitle = ($lang === 'ku' && !empty($edu['title_ku'])) ? $edu['title_ku'] : ($edu['title'] ?? '');
                            $eduInst = ($lang === 'ku' && !empty($edu['institution_ku'])) ? $edu['institution_ku'] : ($edu['institution'] ?? '');
                            $eduDesc = ($lang === 'ku' && !empty($edu['description_ku'])) ? $edu['description_ku'] : ($edu['description'] ?? '');
                        ?>
                        <div class="relative pl-8 border-l-2 border-purple-500/30">
                            <div class="absolute left-0 top-0 w-4 h-4 -translate-x-1/2 rounded-full gradient-bg"></div>
                            <div class="glass-card rounded-xl p-6">
                                <span class="text-purple-600 dark:text-purple-400 text-sm font-medium"><?= htmlspecialchars($edu['period'] ?? '') ?></span>
                                <h4 class="text-xl font-bold mt-2"><?= htmlspecialchars($eduTitle) ?></h4>
                                <p class="text-gray-500 dark:text-gray-400 mb-3"><?= htmlspecialchars($eduInst) ?></p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm"><?= htmlspecialchars($eduDesc) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($education)): ?>
                        <p class="text-gray-500"><?= t('resume.no_education') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Download Resume Button -->
            <?php $resumeFile = getSetting('resume_file', ''); ?>
            <div class="text-center mt-12 animate-on-scroll">
                <a href="<?= !empty($resumeFile) ? htmlspecialchars($resumeFile) : '#' ?>" <?= !empty($resumeFile) ? 'download' : '' ?> class="inline-flex items-center gap-3 gradient-bg text-white px-8 py-4 rounded-full font-semibold hover:opacity-90 transform hover:scale-105 transition-all duration-300 shadow-lg shadow-purple-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <?= t('resume.download') ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Clients Section -->
    <section id="clients" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('clients.trusted_by') ?> <span class="gradient-text"><?= t('clients.highlight') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
                <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-2xl mx-auto">
                    <?= t('clients.description') ?>
                </p>
            </div>
            
            <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12 lg:gap-16">
                <?php if (!empty($clients)): ?>
                    <?php foreach ($clients as $client): ?>
                    <div class="animate-on-scroll group">
                        <a href="<?= htmlspecialchars($client['website_url'] ?? '#') ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="glass-card rounded-2xl p-6 hover:scale-105 transition-all duration-300 flex flex-col items-center justify-center min-w-[140px] block cursor-pointer">
                            <?php if (!empty($client['logo_url'])): ?>
                            <img src="<?= htmlspecialchars($client['logo_url']) ?>" 
                                 alt="<?= htmlspecialchars(getLocalizedField($client, 'company') ?: $client['name']) ?>"
                                 class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-full grayscale group-hover:grayscale-0 transition-all duration-300">
                            <?php else: ?>
                            <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-2xl font-bold text-purple-500"><?= strtoupper(substr(getLocalizedField($client, 'company') ?: $client['name'], 0, 1)) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($client['company']) || !empty($client['company_ku'])): ?>
                            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400 text-center"><?= htmlspecialchars(getLocalizedField($client, 'company')) ?></p>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p><?= t('clients.no_clients') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('portfolio.my') ?> <span class="gradient-text"><?= t('portfolio.highlight') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
                <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-2xl mx-auto">
                    <?= t('portfolio.description') ?>
                </p>
                <a href="projects.php" class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 font-medium mt-4 hover:gap-3 transition-all">
                    <?= t('portfolio.view_all') ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                    <div class="animate-on-scroll group">
                        <div class="glass-card rounded-2xl overflow-hidden hover:scale-[1.02] transition-all duration-300">
                            <div class="relative h-48 overflow-hidden">
                                <?php 
                                $imgUrl = $project['image_url'];
                                $imgSrc = !empty($imgUrl) ? $imgUrl : 'https://via.placeholder.com/600x400?text=No+Image';
                                ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                     alt="<?= htmlspecialchars(getLocalizedField($project, 'title')) ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                                    <a href="project.php?id=<?= $project['id'] ?>" 
                                       class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-white/30 transition-colors">
                                        <?= t('portfolio.view_details') ?> →
                                    </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars(getLocalizedField($project, 'title')) ?></h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">
                                    <?= htmlspecialchars(getLocalizedField($project, 'description')) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        <p><?= t('portfolio.no_projects') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="py-20 bg-gray-50 dark:bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('blog.latest') ?> <span class="gradient-text"><?= t('blog.highlight') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
                <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-2xl mx-auto">
                    <?= t('blog.description') ?>
                </p>
                <a href="blogs.php" class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 font-medium mt-4 hover:gap-3 transition-all">
                    <?= t('blog.view_all') ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <?php if (!empty($blogs)): ?>
                    <?php foreach ($blogs as $blog): ?>
                    <div class="animate-on-scroll">
                        <a href="blog.php?id=<?= $blog['id'] ?>" class="block group">
                            <article class="glass-card rounded-2xl overflow-hidden hover:scale-[1.02] transition-all duration-300">
                                <?php if (!empty($blog['image_url'])): ?>
                                <div class="h-48 overflow-hidden">
                                    <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                                         alt="<?= htmlspecialchars(getLocalizedField($blog, 'title')) ?>"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                                <?php else: ?>
                                <div class="h-48 bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-purple-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                                <?php endif; ?>
                                <div class="p-6">
                                    <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 text-sm mb-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <?= date('M d, Y', strtotime($blog['created_at'])) ?>
                                    </div>
                                    <h3 class="text-xl font-bold mb-3 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                        <?= htmlspecialchars(getLocalizedField($blog, 'title')) ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-3">
                                        <?= htmlspecialchars(substr(getLocalizedField($blog, 'content'), 0, 150)) ?>...
                                    </p>
                                    <span class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 font-medium mt-4 group-hover:gap-3 transition-all">
                                        <?= t('blog.read_more') ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </span>
                                </div>
                            </article>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        <p><?= t('blog.no_posts') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('contact.get_in') ?> <span class="gradient-text"><?= t('contact.highlight') ?></span></h2>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
                <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-2xl mx-auto">
                    <?= t('contact.description') ?>
                </p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-12 max-w-5xl mx-auto">
                <!-- Contact Info -->
                <div class="animate-on-scroll">
                    <h3 class="text-2xl font-bold mb-6"><?= t('contact.lets_talk') ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">
                        <?= t('contact.talk_description') ?>
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl gradient-bg flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= t('contact.email') ?></p>
                                <a href="mailto:<?= htmlspecialchars(getSetting('contact_email', 'hello@johndoe.com')) ?>" class="font-medium hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= htmlspecialchars(getSetting('contact_email', 'hello@johndoe.com')) ?></a>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl gradient-bg flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= t('contact.location') ?></p>
                                <p class="font-medium"><?= htmlspecialchars(getSetting('contact_location', 'San Francisco, CA')) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl gradient-bg flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= t('contact.phone') ?></p>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', getSetting('contact_phone', '+1234567890'))) ?>" class="font-medium hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= htmlspecialchars(getSetting('contact_phone', '+1 (234) 567-890')) ?></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="mt-10">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"><?= t('contact.follow_me') ?></p>
                        <div class="flex gap-4">
                            <a href="<?= htmlspecialchars(getSetting('social_github', '#')) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            </a>
                            <a href="<?= htmlspecialchars(getSetting('social_linkedin', '#')) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                            <a href="<?= htmlspecialchars(getSetting('social_twitter', '#')) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="animate-on-scroll">
                    <form id="contact-form" class="glass-card rounded-2xl p-8" data-error-message="<?= t('form.generic_error') ?>">
                        <div class="grid sm:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="name" class="block text-sm font-medium mb-2"><?= t('contact.your_name') ?></label>
                                <input type="text" id="name" name="name" required
                                       class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                                       placeholder="<?= t('contact.name_placeholder') ?>">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium mb-2"><?= t('contact.your_email') ?></label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                                       placeholder="<?= t('contact.email_placeholder') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="subject" class="block text-sm font-medium mb-2"><?= t('contact.subject') ?></label>
                            <input type="text" id="subject" name="subject" required
                                   class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                                   placeholder="<?= t('contact.subject_placeholder') ?>">
                        </div>
                        
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium mb-2"><?= t('contact.message') ?></label>
                            <textarea id="message" name="message" rows="5" required
                                      class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"
                                      placeholder="<?= t('contact.message_placeholder') ?>"></textarea>
                        </div>
                        
                        <button type="submit" id="submit-btn"
                                class="w-full gradient-bg text-white py-4 rounded-xl font-semibold hover:opacity-90 transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2">
                                <span id="btn-text"><?= t('contact.send_message') ?></span>
                            <svg id="btn-loader" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        
                        <div id="form-message" class="mt-4 p-4 rounded-lg hidden"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                      <a href="#" class="inline-block">
                        <img src="uploads/logo.png" alt="MIR.CODES" class="h-14 w-auto">
                    </a>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">
                        <?= t('footer.tagline') ?>
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <a href="https://github.com/meer404" target="_blank" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/in/mir-mohammed-rashid-524a78221" target="_blank" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="https://x.com/mirmohammed0?s=21" target="_blank" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/mirmohammed_mira/" target="_blank" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
                
                <div class="text-center md:text-right text-gray-600 dark:text-gray-400 text-sm">
                    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(getSetting('hero_name', 'John Doe')) ?>. <?= t('footer.rights') ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="script.js"></script>
    
    <!-- Structured Data -->
    <?= renderPersonSchema([
        'name' => $seoName,
        'jobTitle' => $seoTitle,
        'description' => $seoDescription,
        'image' => $seoImage,
        'email' => $seoEmail,
        'phone' => $seoPhone,
        'location' => $seoLocation,
        'github' => $seoGithub,
        'linkedin' => $seoLinkedin,
        'twitter' => $seoTwitter,
    ]) ?>
    <?= renderWebsiteSchema([
        'name' => $seoName . ' - Portfolio',
        'description' => $seoDescription,
    ]) ?>
</body>
</html>
