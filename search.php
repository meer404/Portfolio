<?php
/**
 * Search API Endpoint
 * Returns JSON results for projects and blogs matching query
 */

require_once 'lang.php';
require_once 'db.php';

header('Content-Type: application/json');

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Minimum 2 characters required
if (strlen($query) < 2) {
    echo json_encode(['projects' => [], 'blogs' => []]);
    exit;
}

try {
    $db = Database::getInstance();
    $results = $db->search($query, 10);
    
    // Process results for current language
    $lang = getCurrentLanguage();
    
    // Format projects
    foreach ($results['projects'] as &$project) {
        $project['title'] = ($lang === 'ku' && !empty($project['title_ku'])) 
            ? $project['title_ku'] 
            : $project['title'];
        $project['description'] = ($lang === 'ku' && !empty($project['description_ku'])) 
            ? $project['description_ku'] 
            : ($project['description'] ?? '');
        $project['url'] = 'project.php?id=' . $project['id'];
        // Remove raw language fields
        unset($project['title_ku'], $project['description_ku']);
    }
    
    // Format blogs
    foreach ($results['blogs'] as &$blog) {
        $blog['title'] = ($lang === 'ku' && !empty($blog['title_ku'])) 
            ? $blog['title_ku'] 
            : $blog['title'];
        $blog['excerpt'] = ($lang === 'ku' && !empty($blog['content_ku'])) 
            ? substr($blog['content_ku'], 0, 100) 
            : substr($blog['content'] ?? '', 0, 100);
        $blog['url'] = 'blog.php?id=' . $blog['id'];
        // Remove raw language fields
        unset($blog['title_ku'], $blog['content'], $blog['content_ku']);
    }
    
    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}
