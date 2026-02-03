<?php
/**
 * Single Project Detail Page
 * Displays individual project with full details
 */

require_once 'lang.php';
require_once 'db.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: projects.php');
    exit;
}

$projectId = (int)$_GET['id'];

// Fetch the project
try {
    $db = Database::getInstance();
    $project = $db->getProjectById($projectId);
    
    if (!$project) {
        header('Location: projects.php');
        exit;
    }
    
    // Get other projects for "More Projects" section
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id != ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$projectId]);
    $relatedProjects = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: projects.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getDir() ?>" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(substr($project['description'], 0, 160)) ?>">
    <title><?= t('page.project_title', ['title' => htmlspecialchars($project['title'])]) ?></title>
    
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
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
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
        .prose-content {
            line-height: 1.8;
        }
        .prose-content p {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans transition-colors duration-300">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="index.php" class="text-2xl font-bold gradient-text">JD</a>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php#home" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.home') ?></a>
                    <a href="index.php#about" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.about') ?></a>
                    <a href="index.php#portfolio" class="text-purple-600 dark:text-purple-400 font-medium"><?= t('nav.portfolio') ?></a>
                    <a href="index.php#blog" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.blog') ?></a>
                    <a href="index.php#contact" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.contact') ?></a>
                    <a href="<?= langUrl(getOtherLanguage()) ?>" class="px-3 py-1 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition-colors"><?= t('language.switch') ?></a>
                    
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
                <div class="flex md:hidden items-center space-x-2">
                    <button id="theme-toggle-mobile" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="w-5 h-5 moon-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                    <a href="index.php" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Project Content -->
    <article class="pt-24 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="projects.php" class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:gap-3 transition-all mb-8">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <?= t('portfolio.back_to_projects') ?>
            </a>
            
            <!-- Project Header -->
            <header class="mb-8 animate-fade-in">
                <?php if (!empty($project['technologies'])): ?>
                <div class="flex flex-wrap gap-2 mb-4">
                    <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-600/20 text-purple-400 border border-purple-500/30">
                        <?= htmlspecialchars(trim($tech)) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 text-sm mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?= date('F d, Y', strtotime($project['created_at'])) ?>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                    <?= htmlspecialchars(getLocalizedField($project, 'title')) ?>
                </h1>
            </header>
            
            <!-- Featured Image -->
            <?php 
            $imgUrl = $project['image_url'];
            if (!empty($imgUrl)): 
            ?>
            <div class="mb-10 animate-fade-in" style="animation-delay: 0.1s;">
                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                     alt="<?= htmlspecialchars(getLocalizedField($project, 'title')) ?>"
                     class="w-full h-64 sm:h-80 lg:h-96 object-cover rounded-2xl shadow-lg">
            </div>
            <?php endif; ?>
            
            <!-- Project Description -->
            <div class="prose-content text-gray-600 dark:text-gray-300 text-lg animate-fade-in" style="animation-delay: 0.2s;">
                <?= nl2br(htmlspecialchars(getLocalizedField($project, 'description'))) ?>
            </div>
            
            <!-- Full Content (if available) -->
            <?php if (!empty(getLocalizedField($project, 'content'))): ?>
            <div class="mt-8 prose-content text-gray-600 dark:text-gray-300 text-lg animate-fade-in" style="animation-delay: 0.25s;">
                <?= nl2br(htmlspecialchars(getLocalizedField($project, 'content'))) ?>
            </div>
            <?php endif; ?>
            
            <!-- Project Links -->
            <?php if (!empty($project['project_link']) || !empty($project['github_link'])): ?>
            <div class="mt-10 flex flex-wrap gap-4 animate-fade-in" style="animation-delay: 0.3s;">
                <?php if (!empty($project['project_link'])): ?>
                <a href="<?= htmlspecialchars($project['project_link']) ?>" 
                   target="_blank"
                   class="inline-flex items-center gap-3 gradient-bg text-white px-8 py-4 rounded-full font-semibold hover:opacity-90 transform hover:scale-105 transition-all duration-300 shadow-lg shadow-purple-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <?= t('portfolio.view_live') ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($project['github_link'])): ?>
                <a href="<?= htmlspecialchars($project['github_link']) ?>" 
                   target="_blank"
                   class="inline-flex items-center gap-3 bg-gray-800 hover:bg-gray-700 text-white px-8 py-4 rounded-full font-semibold transform hover:scale-105 transition-all duration-300 border border-gray-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <?= t('portfolio.view_github') ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Share Section -->
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"><?= t('portfolio.share_project') ?></p>
                <div class="flex gap-3">
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($project['title']) ?>" 
                       target="_blank"
                       class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($project['title']) ?>" 
                       target="_blank"
                       class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                       target="_blank"
                       class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </article>

    <!-- Related Projects -->
    <?php if (!empty($relatedProjects)): ?>
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8"><?= t('portfolio.more_projects') ?></h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($relatedProjects as $related): ?>
                <a href="project.php?id=<?= $related['id'] ?>" class="group">
                    <article class="glass-card rounded-2xl overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <?php 
                        $relatedImg = $related['image_url'];
                        if (!empty($relatedImg)): 
                        ?>
                        <div class="h-40 overflow-hidden">
                            <img src="<?= htmlspecialchars($relatedImg) ?>" 
                                 alt="<?= htmlspecialchars($related['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <?php else: ?>
                        <div class="h-40 bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                            <svg class="w-12 h-12 text-purple-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <div class="p-5">
                            <h3 class="font-bold mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                <?= htmlspecialchars($related['title']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">
                                <?= htmlspecialchars(substr($related['description'], 0, 100)) ?>...
                            </p>
                        </div>
                    </article>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="py-12 bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                    <a href="index.php" class="text-2xl font-bold gradient-text">JD</a>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">
                        <?= t('footer.tagline') ?>
                    </p>
                </div>
                
                <div class="text-center md:text-right text-gray-600 dark:text-gray-400 text-sm">
                    <p>&copy; <?= date('Y') ?>. <?= t('footer.rights') ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleMobile = document.getElementById('theme-toggle-mobile');
        const html = document.documentElement;
        
        function toggleTheme() {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        }
        
        if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
        if (themeToggleMobile) themeToggleMobile.addEventListener('click', toggleTheme);
        
        // Check for saved theme preference
        if (localStorage.getItem('theme') === 'light') {
            html.classList.remove('dark');
        }
    </script>
</body>
</html>
