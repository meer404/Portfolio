/**
 * Portfolio Website JavaScript
 * Handles mobile menu, dark mode, smooth scroll, and contact form
 */

function initApp() {
    console.log('Portfolio App Initializing...');

    // Mobile Menu Toggle
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');

    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // Close menu when clicking on a link
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            });
        });
    }

    // Dark Mode Toggle
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeToggleMobile = document.getElementById('theme-toggle-mobile');
    const htmlElement = document.documentElement;

    // Check for saved theme preference or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    htmlElement.classList.toggle('dark', savedTheme === 'dark');
    updateThemeIcon(savedTheme === 'dark');

    function toggleTheme() {
        const isDark = htmlElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeIcon(isDark);
    }

    function updateThemeIcon(isDark) {
        const sunIcons = document.querySelectorAll('.sun-icon');
        const moonIcons = document.querySelectorAll('.moon-icon');

        sunIcons.forEach(icon => icon.classList.toggle('hidden', !isDark));
        moonIcons.forEach(icon => icon.classList.toggle('hidden', isDark));
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }
    if (themeToggleMobile) {
        themeToggleMobile.addEventListener('click', toggleTheme);
    }

    // Smooth Scrolling for Navigation Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            try {
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    const headerOffset = 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            } catch (err) {
                // Ignore
            }
        });
    });

    // Contact Form AJAX Submission
    const contactForm = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    if (contactForm) {
        contactForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Show loading state
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');
            formMessage.classList.add('hidden');

            const formData = new FormData(contactForm);

            try {
                const response = await fetch('process_contact.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Show message
                formMessage.classList.remove('hidden');
                if (data.success) {
                    formMessage.className = 'mt-4 p-4 rounded-lg bg-green-500/20 text-green-400 border border-green-500/30';
                    formMessage.textContent = data.message;
                    contactForm.reset();
                } else {
                    formMessage.className = 'mt-4 p-4 rounded-lg bg-red-500/20 text-red-400 border border-red-500/30';
                    formMessage.textContent = data.message;
                }
            } catch (error) {
                formMessage.classList.remove('hidden');
                formMessage.className = 'mt-4 p-4 rounded-lg bg-red-500/20 text-red-400 border border-red-500/30';
                formMessage.textContent = contactForm.dataset.errorMessage || 'An error occurred. Please try again.';
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
            }
        });
    }

    // Navbar background on scroll
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('bg-opacity-95', 'shadow-lg');
            } else {
                navbar.classList.remove('bg-opacity-95', 'shadow-lg');
            }
        });
    }

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // === Search Functionality ===
    const searchModal = document.getElementById('search-modal');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const searchBtn = document.getElementById('search-btn');
    const searchBtnMobile = document.getElementById('search-btn-mobile');
    const searchClose = document.getElementById('search-close');
    const searchOverlay = document.getElementById('search-overlay');

    console.log('Search Elements Check:', {
        modal: !!searchModal,
        input: !!searchInput,
        btn: !!searchBtn,
        btnMobile: !!searchBtnMobile
    });

    // Get translation strings from data attributes or defaults
    const noResultsText = searchResults?.dataset.noResults || 'No results found';
    const projectsText = searchResults?.dataset.projectsLabel || 'Projects';
    const blogsText = searchResults?.dataset.blogsLabel || 'Blog Posts';

    let searchTimeout = null;

    function openSearchModal() {
        console.log('Opening search modal');
        if (searchModal) {
            searchModal.classList.remove('hidden');
            setTimeout(() => {
                searchInput?.focus();
            }, 50);
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Search modal not found');
        }
    }

    function closeSearchModal() {
        console.log('Closing search modal');
        if (searchModal) {
            searchModal.classList.add('hidden');
            document.body.style.overflow = '';
            if (searchInput) searchInput.value = '';
            if (searchResults) {
                searchResults.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">Ctrl+K to search</p>';
            }
        }
    }

    // Open modal handlers
    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openSearchModal();
        });
    } else {
        console.warn('Desktop search button not found');
    }

    if (searchBtnMobile) {
        searchBtnMobile.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openSearchModal();
        });
    }

    // Close modal handlers
    if (searchClose) {
        searchClose.addEventListener('click', closeSearchModal);
    }
    if (searchOverlay) {
        searchOverlay.addEventListener('click', closeSearchModal);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Ctrl+K or Cmd+K to open
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchModal?.classList.contains('hidden')) {
                openSearchModal();
            } else {
                closeSearchModal();
            }
        }
        // Escape to close
        if (e.key === 'Escape' && searchModal && !searchModal.classList.contains('hidden')) {
            closeSearchModal();
        }
    });

    // Search input handler with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">Type at least 2 characters to search</p>';
                return;
            }

            searchResults.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-8">Searching...</p>';

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
    }

    async function performSearch(query) {
        try {
            console.log('Performing search for:', query);
            const response = await fetch(`search.php?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            renderSearchResults(data);
        } catch (error) {
            console.error('Search error:', error);
            if (searchResults) {
                searchResults.innerHTML = '<p class="text-red-500 text-center py-8">Search failed. Please try again.</p>';
            }
        }
    }

    function renderSearchResults(data) {
        const hasProjects = data.projects && data.projects.length > 0;
        const hasBlogs = data.blogs && data.blogs.length > 0;

        if (!hasProjects && !hasBlogs) {
            searchResults.innerHTML = `<p class="text-gray-500 dark:text-gray-400 text-center py-8">${noResultsText}</p>`;
            return;
        }

        let html = '';

        // Projects section
        if (hasProjects) {
            html += `<div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">${projectsText}</h3>
                <div class="space-y-2">`;

            data.projects.forEach(project => {
                html += `
                    <a href="${project.url}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-purple-600/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 dark:text-white truncate">${escapeHtml(project.title)}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">${escapeHtml(project.description || '')}</p>
                        </div>
                    </a>`;
            });

            html += '</div></div>';
        }

        // Blogs section
        if (hasBlogs) {
            html += `<div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">${blogsText}</h3>
                <div class="space-y-2">`;

            data.blogs.forEach(blog => {
                html += `
                    <a href="${blog.url}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-pink-600/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-gray-900 dark:text-white truncate">${escapeHtml(blog.title)}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">${escapeHtml(blog.excerpt || '')}</p>
                        </div>
                    </a>`;
            });

            html += '</div></div>';
        }

        searchResults.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Robust initialization handling that works even if DOMContentLoaded has already fired
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            })
            .catch(err => {
                console.log('ServiceWorker registration failed: ', err);
            });
    });
}

// PWA Install Prompt
let deferredPrompt;
const installBtn = document.getElementById('install-btn');
const installBtnMobile = document.getElementById('install-btn-mobile');

// Detect iOS
const isIos = /iPhone|iPad|iPod/.test(navigator.userAgent) && !window.MSStream;
const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;

// Show install button on iOS if not installed
if (isIos && !isStandalone) {
    if (installBtn) installBtn.classList.remove('hidden');
    if (installBtnMobile) installBtnMobile.classList.remove('hidden');
}

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
    // Update UI to notify the user they can add to home screen
    if (installBtn) installBtn.classList.remove('hidden');
    if (installBtnMobile) installBtnMobile.classList.remove('hidden');

    console.log('beforeinstallprompt fired');
});

function showIosInstallInstructions() {
    const div = document.createElement('div');
    div.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4';
    div.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-sm w-full shadow-2xl relative animate-fade-in">
            <button onclick="this.closest('.fixed').remove()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Install App</h3>
            <p class="mb-4 text-gray-600 dark:text-gray-300">To install this app on your iPhone or iPad:</p>
            <ol class="list-decimal list-inside space-y-3 text-gray-600 dark:text-gray-300 mb-4 text-sm">
                <li class="flex items-center gap-2">Tap the <strong>Share</strong> button <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg></li>
                <li class="flex items-center gap-2">Scroll down and tap <strong>Add to Home Screen</strong> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></li>
            </ol>
        </div>
    `;
    document.body.appendChild(div);
}

function handleInstallClick() {
    // If iOS and no deferred prompt, show instructions
    if (isIos && !deferredPrompt) {
        showIosInstallInstructions();
        return;
    }

    if (installBtn) installBtn.classList.add('hidden');
    if (installBtnMobile) installBtnMobile.classList.add('hidden');

    // Show the prompt
    if (deferredPrompt) {
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
        });
    }
}

if (installBtn) {
    installBtn.addEventListener('click', handleInstallClick);
}

if (installBtnMobile) {
    installBtnMobile.addEventListener('click', handleInstallClick);
}

window.addEventListener('appinstalled', (evt) => {
    // Log install to analytics
    console.log('INSTALL: Success');
});
