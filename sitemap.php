<?php
/**
 * Dynamic XML Sitemap Generator
 * Generates a sitemap with all public pages for search engines
 */

require_once 'db.php';

header('Content-Type: application/xml; charset=utf-8');

// Get base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host . dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($baseUrl, '/');

// Fetch data
try {
    $db = Database::getInstance();
    $projects = $db->getProjects();
    $blogs = $db->getBlogs();
} catch (Exception $e) {
    $projects = [];
    $blogs = [];
}

// Current date for lastmod
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    
    <!-- Homepage -->
    <url>
        <loc><?= $baseUrl ?>/index.php</loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/index.php?lang=en"/>
        <xhtml:link rel="alternate" hreflang="ku" href="<?= $baseUrl ?>/index.php?lang=ku"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/index.php"/>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- All Projects Page -->
    <url>
        <loc><?= $baseUrl ?>/projects.php</loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/projects.php?lang=en"/>
        <xhtml:link rel="alternate" hreflang="ku" href="<?= $baseUrl ?>/projects.php?lang=ku"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/projects.php"/>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- All Blogs Page -->
    <url>
        <loc><?= $baseUrl ?>/blogs.php</loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/blogs.php?lang=en"/>
        <xhtml:link rel="alternate" hreflang="ku" href="<?= $baseUrl ?>/blogs.php?lang=ku"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/blogs.php"/>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- Individual Projects -->
    <?php foreach ($projects as $project): 
        $projectDate = !empty($project['updated_at']) ? date('Y-m-d', strtotime($project['updated_at'])) : date('Y-m-d', strtotime($project['created_at']));
    ?>
    <url>
        <loc><?= $baseUrl ?>/project.php?id=<?= $project['id'] ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/project.php?id=<?= $project['id'] ?>&amp;lang=en"/>
        <xhtml:link rel="alternate" hreflang="ku" href="<?= $baseUrl ?>/project.php?id=<?= $project['id'] ?>&amp;lang=ku"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/project.php?id=<?= $project['id'] ?>"/>
        <lastmod><?= $projectDate ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Individual Blog Posts -->
    <?php foreach ($blogs as $blog): 
        $blogDate = !empty($blog['updated_at']) ? date('Y-m-d', strtotime($blog['updated_at'])) : date('Y-m-d', strtotime($blog['created_at']));
    ?>
    <url>
        <loc><?= $baseUrl ?>/blog.php?id=<?= $blog['id'] ?></loc>
        <xhtml:link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/blog.php?id=<?= $blog['id'] ?>&amp;lang=en"/>
        <xhtml:link rel="alternate" hreflang="ku" href="<?= $baseUrl ?>/blog.php?id=<?= $blog['id'] ?>&amp;lang=ku"/>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/blog.php?id=<?= $blog['id'] ?>"/>
        <lastmod><?= $blogDate ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
</urlset>
