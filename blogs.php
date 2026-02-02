<?php
/**
 * All Blogs Page
 * Displays all blog posts
 */

require_once 'lang.php';
require_once 'db.php';

// Fetch all blogs from database
try {
    $db = Database::getInstance();
    $blogs = $db->getBlogs();  // Get all blogs
} catch (Exception $e) {
    $blogs = [];
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getDir() ?>" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse all blog posts with thoughts, tutorials, and insights on web development">
    <title>All Blog Posts | Portfolio</title>
    
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
                    <a href="index.php#portfolio" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"><?= t('nav.portfolio') ?></a>
                    <a href="index.php#blog" class="text-purple-600 dark:text-purple-400 font-medium"><?= t('nav.blog') ?></a>
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

    <!-- Blogs Section -->
    <section class="pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="index.php#blog" class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:gap-3 transition-all mb-8">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <?= t('blog.back_to_home') ?>
            </a>
            
            <!-- Header -->
            <div class="text-center mb-16 animate-fade-in">
                <h1 class="text-3xl sm:text-4xl font-bold mb-4"><?= t('blog.all_posts') ?> <span class="gradient-text"><?= t('blog.all_posts_highlight') ?></span></h1>
                <div class="w-20 h-1 gradient-bg mx-auto rounded-full"></div>
                <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-2xl mx-auto">
                    <?= t('blog.description') ?>
                </p>
            </div>
            
            <!-- Blogs Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($blogs)): ?>
                    <?php foreach ($blogs as $index => $blog): ?>
                    <div class="animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s;">
                        <a href="blog.php?id=<?= $blog['id'] ?>" class="block group">
                            <article class="glass-card rounded-2xl overflow-hidden hover:scale-[1.02] transition-all duration-300">
                                <?php if (!empty($blog['image_url'])): ?>
                                <div class="h-48 overflow-hidden">
                                    <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($blog['title']) ?>"
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
                                        <?= htmlspecialchars($blog['title']) ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-3">
                                        <?= htmlspecialchars(substr($blog['content'], 0, 150)) ?>...
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
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <p><?= t('blog.no_posts') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

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
