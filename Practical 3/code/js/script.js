/**
 * ==============================================================================
 * StudentHub — Practical 4: JavaScript Interactivity & DOM Manipulation
 * ==============================================================================
 * Description: Shared global JavaScript backing all 10 pages of the StudentHub
 *              portal. Provides DOM selection, event listeners, class manipulation,
 *              timers, localStorage/sessionStorage state persistence, and UI logic.
 *
 * Modules:
 * 1. Immediate Theme Preloader (Prevents Theme Flash)
 * 2. Dark / Light Mode Theme Toggle (All Pages)
 * 3. Mobile Hamburger Navigation Menu (index.html)
 * 4. Notification Banner with Session Memory (index.html)
 * 5. Hero Image & Content Carousel Slider (index.html)
 * 6. Quick Login Modal Popup & Form Handling (index.html)
 * 7. Homepage FAQ Accordion with Exclusivity (index.html)
 * ==============================================================================
 */

// ==============================================================================
// 1. IMMEDIATE THEME PRELOADER
// ==============================================================================
// Run immediately before DOM paint to apply the saved theme without visual flash
(function applyImmediateTheme() {
    try {
        const savedTheme = localStorage.getItem('studenthub_theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (document.body) {
                document.body.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-theme');
            }
        }
    } catch (e) {
        console.warn('StudentHub: LocalStorage not accessible for theme preloading.', e);
    }
})();

// ==============================================================================
// 2. THEME SWITCHER (DARK / LIGHT MODE)
// ==============================================================================
/**
 * Initializes Dark / Light theme toggling across all pages.
 * Supports #theme-toggle, .theme-btn, and .floating-theme-btn.
 * Persists user preference in localStorage under 'studenthub_theme'.
 */
function initThemeToggle() {
    const themeButtons = document.querySelectorAll('#theme-toggle, .theme-btn');
    if (!themeButtons.length) return;

    // Helper: Update icon class inside all theme toggle buttons
    function updateThemeIcons(isDark) {
        themeButtons.forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                if (isDark) {
                    icon.className = 'fa-solid fa-sun';
                    btn.setAttribute('aria-label', 'Switch to Light Mode');
                } else {
                    icon.className = 'fa-solid fa-moon';
                    btn.setAttribute('aria-label', 'Switch to Dark Mode');
                }
            }
        });
    }

    // Helper: Apply theme to DOM and save to localStorage
    function setTheme(theme) {
        const isDark = (theme === 'dark');
        if (isDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-theme');
            localStorage.setItem('studenthub_theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.body.removeAttribute('data-theme');
            document.body.classList.remove('dark-theme');
            localStorage.setItem('studenthub_theme', 'light');
        }
        updateThemeIcons(isDark);
    }

    // Sync initial state from localStorage or existing attribute
    const currentTheme = localStorage.getItem('studenthub_theme') || 
        (document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
    setTheme(currentTheme);

    // Attach click event listeners to all theme buttons
    themeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const nextTheme = (activeTheme === 'dark') ? 'light' : 'dark';
            setTheme(nextTheme);
        });
    });
}

// ==============================================================================
// 3. MOBILE HAMBURGER MENU (index.html)
// ==============================================================================
/**
 * Controls off-canvas navigation menu on mobile/tablet viewports.
 * Toggles 'active' class on #nav-links and swaps fa-bars <-> fa-xmark.
 * Automatically closes menu upon link selection or viewport resize > 768px.
 */
function initHamburgerMenu() {
    const hamburger = document.getElementById('hamburger-menu');
    const navLinks = document.getElementById('nav-links');
    if (!hamburger || !navLinks) return;

    // Helper: Close mobile navigation
    function closeMenu() {
        navLinks.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
        const icon = hamburger.querySelector('i');
        if (icon) {
            icon.className = 'fa-solid fa-bars';
        }
    }

    // Toggle menu state on hamburger click
    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = navLinks.classList.toggle('active');
        hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        const icon = hamburger.querySelector('i');
        if (icon) {
            icon.className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        }
    });

    // Close menu when any navigation link is clicked
    const links = navLinks.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', () => {
            closeMenu();
        });
    });

    // Close menu if user clicks anywhere outside the navigation drawer
    document.addEventListener('click', (e) => {
        if (navLinks.classList.contains('active') && 
            !navLinks.contains(e.target) && 
            !hamburger.contains(e.target)) {
            closeMenu();
        }
    });

    // Reset menu on viewport resize above mobile breakpoint (768px)
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
            closeMenu();
        }
    });
}

// ==============================================================================
// 4. NOTIFICATION BANNER (index.html)
// ==============================================================================
/**
 * Manages the top announcement banner on the home page.
 * Auto-displays banner on page load unless user dismissed it in the current session.
 * Stores dismissal flag in sessionStorage under 'studenthub_banner_dismissed'.
 */
function initNotificationBanner() {
    const banner = document.getElementById('notification-banner');
    const closeBtn = document.getElementById('close-banner');
    if (!banner || !closeBtn) return;

    // Check session dismissal state
    const isDismissed = sessionStorage.getItem('studenthub_banner_dismissed');

    if (isDismissed !== 'true') {
        // Smoothly reveal banner on page load
        setTimeout(() => {
            banner.classList.remove('hidden');
        }, 150);
    }

    // Handle close button click
    closeBtn.addEventListener('click', () => {
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-100%)';
        setTimeout(() => {
            banner.classList.add('hidden');
            banner.style.opacity = '';
            banner.style.transform = '';
        }, 300);
        sessionStorage.setItem('studenthub_banner_dismissed', 'true');
    });
}

// ==============================================================================
// 5. HERO IMAGE & CONTENT SLIDER (index.html)
// ==============================================================================
/**
 * Interactive carousel slider with manual controls, dot pagination,
 * automatic timing progression, and hover-pause capability.
 */
function initHeroSlider() {
    const slider = document.getElementById('slider');
    const prevBtn = document.getElementById('slider-prev');
    const nextBtn = document.getElementById('slider-next');
    const dotsContainer = document.getElementById('slider-dots');
    
    if (!slider) return;

    const slides = slider.querySelectorAll('.slide');
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.dot') : [];
    if (!slides.length) return;

    let currentIndex = 0;
    let autoSlideInterval = null;
    const AUTO_DELAY = 5000; // 5 seconds per slide

    // Function: Transition to specific slide index
    function goToSlide(index) {
        // Wrap around boundaries
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }

        currentIndex = index;

        // Update slides visibility
        slides.forEach((slide, idx) => {
            if (idx === currentIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });

        // Update dot indicators
        dots.forEach((dot, idx) => {
            if (idx === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    // Next / Previous slide actions
    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    // Auto-advance timer management
    function startAutoSlide() {
        stopAutoSlide();
        autoSlideInterval = setInterval(nextSlide, AUTO_DELAY);
    }

    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
            autoSlideInterval = null;
        }
    }

    function resetTimer() {
        stopAutoSlide();
        startAutoSlide();
    }

    // Event Listeners for Next & Prev Buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            resetTimer();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            resetTimer();
        });
    }

    // Event Listeners for Dot Indicators
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            const targetIndex = parseInt(e.target.getAttribute('data-index'), 10);
            if (!isNaN(targetIndex)) {
                goToSlide(targetIndex);
                resetTimer();
            }
        });
    });

    // Pause slider on mouse hover for comfortable reading
    const heroSlider = slider.closest('.hero-slider') || slider;
    heroSlider.addEventListener('mouseenter', stopAutoSlide);
    heroSlider.addEventListener('mouseleave', startAutoSlide);

    // Initialize first slide and start timer
    goToSlide(0);
    startAutoSlide();
}

// ==============================================================================
// 6. LOGIN MODAL POPUP & VALIDATION (index.html)
// ==============================================================================
/**
 * Manages modal window opening, backdrop dismissal, escape key listening,
 * and demo form submission handling.
 */
function initLoginModal() {
    const modal = document.getElementById('login-modal');
    const openBtn = document.getElementById('open-modal-btn');
    if (!modal || !openBtn) return;

    const closeBtn = modal.querySelector('.close-modal');
    const modalForm = document.getElementById('modal-login-form');

    // Function: Show modal window
    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        const firstInput = modal.querySelector('input');
        if (firstInput) firstInput.focus();
    }

    // Function: Hide modal window
    function closeModal() {
        modal.classList.remove('active');
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore background scrolling
    }

    // Trigger button listener
    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal();
    });

    // Close button listener
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    }

    // Backdrop click listener (outside modal content card)
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Keyboard ESC key listener
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Form submission validation & simulated demo feedback
    if (modalForm) {
        modalForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const studentIdInput = document.getElementById('student-id');
            const passwordInput = document.getElementById('password');

            const studentId = studentIdInput ? studentIdInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value.trim() : '';

            if (!studentId || !password) {
                alert('Please enter both your Student ID and Password.');
                return;
            }

            // Success feedback
            alert(`Welcome back, ${studentId}! Login authentication simulated successfully.`);
            modalForm.reset();
            closeModal();
        });
    }
}

// ==============================================================================
// 7. HOMEPAGE FAQ ACCORDION (index.html)
// ==============================================================================
/**
 * Interactive FAQ Accordion for index.html (button.faq-question + div.faq-answer).
 * Features exclusive single-open accordion behavior with smooth height transitions.
 */
function initHomeFaqAccordion() {
    const faqContainer = document.querySelector('.faq-section .faq-container');
    if (!faqContainer) return;

    const faqItems = faqContainer.querySelectorAll('.faq-item');
    if (!faqItems.length) return;

    faqItems.forEach(item => {
        const questionBtn = item.querySelector('.faq-question');
        if (!questionBtn) return;

        questionBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isCurrentlyOpen = item.classList.contains('active') || item.classList.contains('open');

            // Exclusivity: Close all FAQ items first
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active', 'open');
            });

            // Toggle current item
            if (!isCurrentlyOpen) {
                item.classList.add('active', 'open');
            }
        });
    });
}

// ==============================================================================
// 8. GLOBAL INITIALIZATION ENTRY POINT
// ==============================================================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Universal Theme Toggle
    initThemeToggle();

    // 2. Mobile Navigation Drawer
    initHamburgerMenu();

    // 3. Announcement Banner
    initNotificationBanner();

    // 4. Hero Carousel Slider
    initHeroSlider();

    // 5. Login Modal Window
    initLoginModal();

    // 6. Home FAQ Accordion
    initHomeFaqAccordion();
});
