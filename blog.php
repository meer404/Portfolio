<?php
/**
 * Blog Post Detail Page
 * Displays individual blog post with full content
 */

require_once 'lang.php';
require_once 'db.php';
require_once 'seo.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php#blog');
    exit;
}

$blogId = (int)$_GET['id'];

// Fetch the blog post
try {
    $db = Database::getInstance();
    $blog = $db->getBlogById($blogId);
    
    if (!$blog) {
        header('Location: index.php#blog');
        exit;
    }
    
    // Get other recent posts for "More Posts" section
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id != ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$blogId]);
    $relatedPosts = $stmt->fetchAll();
    
    // Get site settings for author info
    $settings = $db->getSettings(['hero_name']);
    $authorName = $settings['hero_name'] ?? 'Author';
    
} catch (Exception $e) {
    header('Location: index.php#blog');
    exit;
}

// SEO variables
$seoTitle = getLocalizedField($blog, 'title');
$seoContent = getLocalizedField($blog, 'content');
$seoImage = $blog['image_url'] ?? '';
$seoPublished = date('c', strtotime($blog['created_at']));
$seoModified = !empty($blog['updated_at']) ? date('c', strtotime($blog['updated_at'])) : $seoPublished;
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getDir() ?>" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= renderSeoMeta([
        'title' => $seoTitle . ' | ' . t('nav.blog'),
        'description' => mb_substr(strip_tags($seoContent), 0, 160, 'UTF-8'),
        'keywords' => getDefaultKeywords('blog') . ', ' . $seoTitle,
        'image' => $seoImage,
        'type' => 'article',
        'author' => $authorName,
        'published_time' => $seoPublished,
        'modified_time' => $seoModified,
        'section' => t('nav.blog'),
    ]) ?>
    <title><?= t('page.blog_title', ['title' => htmlspecialchars($seoTitle)]) ?></title>
    
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
                <div class="hidden md:flex items-center gap-8">
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
                <div class="flex md:hidden items-center gap-2">
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

    <!-- Blog Post Content -->
    <article class="pt-24 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <a href="index.php#blog" class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:gap-3 transition-all mb-8">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <?= t('blog.back_to_blog') ?>
            </a>
            
            <!-- Post Header -->
            <header class="mb-8 animate-fade-in">
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 text-sm mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?= date('F d, Y', strtotime($blog['created_at'])) ?>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                    <?= htmlspecialchars(getLocalizedField($blog, 'title')) ?>
                </h1>
            </header>
            
            <!-- Featured Image -->
            <?php if (!empty($blog['image_url'])): ?>
            <div class="mb-10 animate-fade-in" style="animation-delay: 0.1s;">
                <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                     alt="<?= htmlspecialchars(getLocalizedField($blog, 'title')) ?>"
                     class="w-full h-64 sm:h-80 lg:h-96 object-cover rounded-2xl shadow-lg">
            </div>
            <?php endif; ?>
            
            <!-- Post Content -->
            <div class="prose-content text-gray-600 dark:text-gray-300 text-lg animate-fade-in" style="animation-delay: 0.2s;">
                <?= nl2br(htmlspecialchars(getLocalizedField($blog, 'content'))) ?>
            </div>
            
            <!-- Share Section -->
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"><?= t('blog.share_post') ?></p>
                <div class="flex gap-3">
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($blog['title']) ?>" 
                       target="_blank"
                       class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($blog['title']) ?>" 
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

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8"><?= t('blog.more_posts') ?></h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($relatedPosts as $post): ?>
                <a href="blog.php?id=<?= $post['id'] ?>" class="group">
                    <article class="glass-card rounded-2xl overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <?php if (!empty($post['image_url'])): ?>
                        <div class="h-40 overflow-hidden">
                            <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <?php else: ?>
                        <div class="h-40 bg-gradient-to-br from-purple-600/20 to-pink-600/20 flex items-center justify-center">
                            <svg class="w-12 h-12 text-purple-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <div class="p-5">
                            <div class="text-purple-600 dark:text-purple-400 text-xs mb-2">
                                <?= date('M d, Y', strtotime($post['created_at'])) ?>
                            </div>
                            <h3 class="font-bold mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                <?= htmlspecialchars($post['title']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2">
                                <?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...
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
    
    <!-- Structured Data -->
    <?= renderBlogPostSchema([
        'title' => $seoTitle,
        'description' => $seoContent,
        'image' => $seoImage,
        'datePublished' => $seoPublished,
        'dateModified' => $seoModified,
        'author' => $authorName,
        'publisher' => $authorName,
    ]) ?>
    <?= renderBreadcrumbSchema([
        ['name' => t('nav.home'), 'url' => '/index.php'],
        ['name' => t('nav.blog'), 'url' => '/blogs.php'],
        ['name' => $seoTitle, 'url' => '/blog.php?id=' . $blogId],
    ]) ?>
</body>
</html>
