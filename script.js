/**
 * Portfolio Website JavaScript
 * Handles mobile menu, dark mode, smooth scroll, and contact form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');

    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
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
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
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
        });
    });

    // Contact Form AJAX Submission
    const contactForm = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');

    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
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
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('bg-opacity-95', 'shadow-lg');
        } else {
            navbar.classList.remove('bg-opacity-95', 'shadow-lg');
        }
    });

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
});
