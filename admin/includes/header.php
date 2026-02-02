<?php
/**
 * Admin Header Include
 */
require_once __DIR__ . '/../auth.php';
Auth::requireLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> | Portfolio Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar-link.active { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border-left: 3px solid #a78bfa; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100">
    <div class="flex min-h-screen">
