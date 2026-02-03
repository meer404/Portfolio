<?php
/**
 * SEO Helper Functions
 * Centralized SEO management for the portfolio website
 */

/**
 * Get the base URL of the website
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

/**
 * Get the current full URL
 */
function getCurrentUrl() {
    return getBaseUrl() . $_SERVER['REQUEST_URI'];
}

/**
 * Get canonical URL (without query params except lang)
 */
function getCanonicalUrl() {
    $baseUrl = getBaseUrl();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $lang = getCurrentLanguage();
    
    if ($lang !== 'en') {
        return $baseUrl . $path . '?lang=' . $lang;
    }
    return $baseUrl . $path;
}

/**
 * Generate SEO meta tags
 */
function renderSeoMeta($options = []) {
    $defaults = [
        'title' => 'Portfolio',
        'description' => 'Professional portfolio showcasing web development projects and skills',
        'keywords' => 'web developer, portfolio, full-stack developer, PHP, JavaScript, web design',
        'image' => '',
        'type' => 'website',
        'author' => '',
        'published_time' => '',
        'modified_time' => '',
        'section' => '',
        'noindex' => false,
    ];
    
    $opts = array_merge($defaults, $options);
    $baseUrl = getBaseUrl();
    $currentUrl = getCurrentUrl();
    $canonicalUrl = getCanonicalUrl();
    $lang = getCurrentLanguage();
    $otherLang = getOtherLanguage();
    
    // Default image fallback
    if (empty($opts['image'])) {
        $opts['image'] = $baseUrl . '/uploads/og-default.jpg';
    } elseif (strpos($opts['image'], 'http') !== 0) {
        $opts['image'] = $baseUrl . '/' . ltrim($opts['image'], '/');
    }
    
    // Truncate description to 160 characters
    $opts['description'] = substr(strip_tags($opts['description']), 0, 160);
    
    $html = '';
    
    // Basic Meta Tags
    $html .= '<meta name="description" content="' . htmlspecialchars($opts['description']) . '">' . "\n";
    $html .= '    <meta name="keywords" content="' . htmlspecialchars($opts['keywords']) . '">' . "\n";
    
    if (!empty($opts['author'])) {
        $html .= '    <meta name="author" content="' . htmlspecialchars($opts['author']) . '">' . "\n";
    }
    
    // Robots
    if ($opts['noindex']) {
        $html .= '    <meta name="robots" content="noindex, nofollow">' . "\n";
    } else {
        $html .= '    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
    }
    
    // Canonical URL
    $html .= '    <link rel="canonical" href="' . htmlspecialchars($canonicalUrl) . '">' . "\n";
    
    // Hreflang for multi-language
    $pathWithoutQuery = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $query = [];
    parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '', $query);
    unset($query['lang']);
    $queryString = !empty($query) ? '&' . http_build_query($query) : '';
    
    $html .= '    <link rel="alternate" hreflang="en" href="' . htmlspecialchars($baseUrl . $pathWithoutQuery . '?lang=en' . $queryString) . '">' . "\n";
    $html .= '    <link rel="alternate" hreflang="ku" href="' . htmlspecialchars($baseUrl . $pathWithoutQuery . '?lang=ku' . $queryString) . '">' . "\n";
    $html .= '    <link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($baseUrl . $pathWithoutQuery) . '">' . "\n";
    
    // Open Graph Tags
    $html .= '    <!-- Open Graph / Facebook -->' . "\n";
    $html .= '    <meta property="og:type" content="' . htmlspecialchars($opts['type']) . '">' . "\n";
    $html .= '    <meta property="og:url" content="' . htmlspecialchars($currentUrl) . '">' . "\n";
    $html .= '    <meta property="og:title" content="' . htmlspecialchars($opts['title']) . '">' . "\n";
    $html .= '    <meta property="og:description" content="' . htmlspecialchars($opts['description']) . '">' . "\n";
    $html .= '    <meta property="og:image" content="' . htmlspecialchars($opts['image']) . '">' . "\n";
    $html .= '    <meta property="og:locale" content="' . ($lang === 'ku' ? 'ckb_IQ' : 'en_US') . '">' . "\n";
    $html .= '    <meta property="og:locale:alternate" content="' . ($lang === 'ku' ? 'en_US' : 'ckb_IQ') . '">' . "\n";
    $html .= '    <meta property="og:site_name" content="' . htmlspecialchars($opts['author'] ?: 'Portfolio') . '">' . "\n";
    
    if ($opts['type'] === 'article' && !empty($opts['published_time'])) {
        $html .= '    <meta property="article:published_time" content="' . htmlspecialchars($opts['published_time']) . '">' . "\n";
        if (!empty($opts['modified_time'])) {
            $html .= '    <meta property="article:modified_time" content="' . htmlspecialchars($opts['modified_time']) . '">' . "\n";
        }
        if (!empty($opts['author'])) {
            $html .= '    <meta property="article:author" content="' . htmlspecialchars($opts['author']) . '">' . "\n";
        }
        if (!empty($opts['section'])) {
            $html .= '    <meta property="article:section" content="' . htmlspecialchars($opts['section']) . '">' . "\n";
        }
    }
    
    // Twitter Card Tags
    $html .= '    <!-- Twitter -->' . "\n";
    $html .= '    <meta name="twitter:card" content="summary_large_image">' . "\n";
    $html .= '    <meta name="twitter:url" content="' . htmlspecialchars($currentUrl) . '">' . "\n";
    $html .= '    <meta name="twitter:title" content="' . htmlspecialchars($opts['title']) . '">' . "\n";
    $html .= '    <meta name="twitter:description" content="' . htmlspecialchars($opts['description']) . '">' . "\n";
    $html .= '    <meta name="twitter:image" content="' . htmlspecialchars($opts['image']) . '">' . "\n";
    
    return $html;
}

/**
 * Generate JSON-LD structured data for a person/developer
 */
function renderPersonSchema($options = []) {
    $baseUrl = getBaseUrl();
    
    $person = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $options['name'] ?? 'John Doe',
        'url' => $baseUrl,
        'jobTitle' => $options['jobTitle'] ?? 'Full-Stack Developer',
        'description' => $options['description'] ?? '',
    ];
    
    if (!empty($options['image'])) {
        $person['image'] = strpos($options['image'], 'http') === 0 ? $options['image'] : $baseUrl . '/' . ltrim($options['image'], '/');
    }
    
    if (!empty($options['email'])) {
        $person['email'] = 'mailto:' . $options['email'];
    }
    
    if (!empty($options['phone'])) {
        $person['telephone'] = $options['phone'];
    }
    
    if (!empty($options['location'])) {
        $person['address'] = [
            '@type' => 'PostalAddress',
            'addressLocality' => $options['location']
        ];
    }
    
    $sameAs = [];
    if (!empty($options['github'])) $sameAs[] = $options['github'];
    if (!empty($options['linkedin'])) $sameAs[] = $options['linkedin'];
    if (!empty($options['twitter'])) $sameAs[] = $options['twitter'];
    
    if (!empty($sameAs)) {
        $person['sameAs'] = $sameAs;
    }
    
    return '<script type="application/ld+json">' . json_encode($person, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate JSON-LD structured data for a website
 */
function renderWebsiteSchema($options = []) {
    $baseUrl = getBaseUrl();
    
    $website = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $options['name'] ?? 'Portfolio',
        'url' => $baseUrl,
        'inLanguage' => ['en', 'ku'],
    ];
    
    if (!empty($options['description'])) {
        $website['description'] = $options['description'];
    }
    
    return '<script type="application/ld+json">' . json_encode($website, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate JSON-LD structured data for a blog post
 */
function renderBlogPostSchema($options = []) {
    $baseUrl = getBaseUrl();
    
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $options['title'] ?? '',
        'description' => substr(strip_tags($options['description'] ?? ''), 0, 160),
        'url' => $options['url'] ?? getCurrentUrl(),
        'datePublished' => $options['datePublished'] ?? '',
        'dateModified' => $options['dateModified'] ?? $options['datePublished'] ?? '',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $options['url'] ?? getCurrentUrl()
        ],
    ];
    
    if (!empty($options['image'])) {
        $article['image'] = [
            '@type' => 'ImageObject',
            'url' => strpos($options['image'], 'http') === 0 ? $options['image'] : $baseUrl . '/' . ltrim($options['image'], '/'),
        ];
    }
    
    if (!empty($options['author'])) {
        $article['author'] = [
            '@type' => 'Person',
            'name' => $options['author']
        ];
    }
    
    if (!empty($options['publisher'])) {
        $article['publisher'] = [
            '@type' => 'Person',
            'name' => $options['publisher']
        ];
    }
    
    return '<script type="application/ld+json">' . json_encode($article, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate JSON-LD structured data for a creative work/project
 */
function renderProjectSchema($options = []) {
    $baseUrl = getBaseUrl();
    
    $project = [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $options['title'] ?? '',
        'description' => substr(strip_tags($options['description'] ?? ''), 0, 160),
        'url' => $options['url'] ?? getCurrentUrl(),
        'datePublished' => $options['datePublished'] ?? '',
    ];
    
    if (!empty($options['image'])) {
        $project['image'] = strpos($options['image'], 'http') === 0 ? $options['image'] : $baseUrl . '/' . ltrim($options['image'], '/');
    }
    
    if (!empty($options['author'])) {
        $project['creator'] = [
            '@type' => 'Person',
            'name' => $options['author']
        ];
    }
    
    if (!empty($options['technologies'])) {
        $project['keywords'] = $options['technologies'];
    }
    
    if (!empty($options['projectUrl'])) {
        $project['mainEntityOfPage'] = $options['projectUrl'];
    }
    
    if (!empty($options['githubUrl'])) {
        $project['codeRepository'] = $options['githubUrl'];
    }
    
    return '<script type="application/ld+json">' . json_encode($project, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Generate JSON-LD breadcrumb schema
 */
function renderBreadcrumbSchema($breadcrumbs = []) {
    $baseUrl = getBaseUrl();
    
    $items = [];
    foreach ($breadcrumbs as $index => $crumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['name'],
            'item' => strpos($crumb['url'], 'http') === 0 ? $crumb['url'] : $baseUrl . $crumb['url']
        ];
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items
    ];
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
