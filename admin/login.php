<?php
/**
 * Admin Login Page
 */

require_once 'auth.php';
require_once 'includes/CSRF.php';
require_once 'includes/Security.php';

Security::headers();

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired or invalid request. Please reload and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            $result = Auth::login($username, $password);
            if ($result['success']) {
                header('Location: index.php');
                exit;
            } else {
                $error = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-block text-4xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent">
                JD
            </a>
            <h1 class="text-2xl font-bold text-white mt-4">Admin Panel</h1>
            <p class="text-gray-400 mt-2">Sign in to manage your portfolio</p>
        </div>

        <!-- Login Card -->
        <div class="bg-gray-900 rounded-2xl p-8 border border-gray-800 shadow-xl">
            <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?php CSRF::renderInput(); ?>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="Enter username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
                           placeholder="Enter password">
                </div>

                <button type="submit" class="w-full gradient-bg text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all duration-300">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            <a href="../index.php" class="text-purple-400 hover:text-purple-300 transition-colors">
                ← Back to Website
            </a>
        </p>
    </div>
</body>
</html>
