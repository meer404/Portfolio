        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 border-r border-gray-800 fixed h-full z-40 transition-transform -translate-x-full md:translate-x-0" id="sidebar">
            <div class="p-6 border-b border-gray-800">
                <a href="index.php" class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent">
                    JD Admin
                </a>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="index.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                
                <a href="projects.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all <?= $currentPage === 'projects' || $currentPage === 'project_form' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Projects
                </a>
                
                <a href="blogs.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all <?= $currentPage === 'blogs' || $currentPage === 'blog_form' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    Blog Posts
                </a>
                
                <a href="messages.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all <?= $currentPage === 'messages' || $currentPage === 'message_view' ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Messages
                    <?php
                    try {
                        $db = Database::getInstance()->getConnection();
                        $unread = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
                        if ($unread > 0):
                    ?>
                    <span class="ml-auto bg-purple-600 text-xs px-2 py-1 rounded-full"><?= $unread ?></span>
                    <?php endif; } catch (Exception $e) {} ?>
                </a>
                
                <div class="pt-4 mt-4 border-t border-gray-800">
                    <a href="../index.php" target="_blank" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View Website
                    </a>
                    
                    <a href="logout.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-500/10 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content Wrapper -->
        <div class="flex-1 md:ml-64">
            <!-- Top Bar -->
            <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
                <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg hover:bg-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <h1 class="text-xl font-bold"><?= $pageTitle ?? 'Dashboard' ?></h1>
                
                <div class="flex items-center gap-4">
                    <span class="text-gray-400 text-sm hidden sm:block">Welcome, <?= htmlspecialchars(Auth::getUsername()) ?></span>
                    <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-sm font-bold">
                        <?= strtoupper(substr(Auth::getUsername(), 0, 1)) ?>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-6">
