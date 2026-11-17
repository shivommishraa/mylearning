import './bootstrap';
import '../css/responsive.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-product-action]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector('button');
    const card = form.closest('.product-card');
    const feedback = card.querySelector('[data-product-feedback]');
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Unable to complete this action.');
        }

        feedback.textContent = result.message;
        feedback.hidden = false;

        if (form.dataset.productAction === 'cart') {
            const cartCount = document.querySelector('.cart-count');

            if (cartCount) {
                cartCount.textContent = result.cart_count;
            }
        } else {
            const isWishlisted = result.is_wishlisted;
            button.classList.toggle('is-saved', isWishlisted);
            button.setAttribute('aria-pressed', String(isWishlisted));
            button.setAttribute('title', isWishlisted ? 'Remove from wishlist' : 'Save to wishlist');
            button.setAttribute(
                'aria-label',
                `${isWishlisted ? 'Remove' : 'Add'} ${button.closest('.product-card').querySelector('.product-title').textContent} ${isWishlisted ? 'from' : 'to'} wishlist`,
            );
        }
    } catch (error) {
        feedback.textContent = error.message || 'Unable to complete this action. Please try again.';
        feedback.hidden = false;
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});

const storeMenuButton = document.querySelector('[data-menu-toggle]');
const storeMenu = document.querySelector('[data-menu]');

if (storeMenuButton && storeMenu) {
    const closeStoreMenu = () => {
        storeMenu.classList.remove('open');
        storeMenuButton.setAttribute('aria-expanded', 'false');
        storeMenuButton.setAttribute('aria-label', 'Open navigation');
    };

    storeMenuButton.addEventListener('click', () => {
        const isOpen = storeMenu.classList.toggle('open');
        storeMenuButton.setAttribute('aria-expanded', String(isOpen));
        storeMenuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    });

    storeMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeStoreMenu);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeStoreMenu();
        }
    });
}

const adminMenuButton = document.querySelector('[data-admin-menu-toggle]');
const adminSidebar = document.querySelector('[data-admin-sidebar]');
const adminMenuClose = document.querySelector('[data-admin-menu-close]');
const adminMenuBackdrop = document.querySelector('[data-admin-menu-backdrop]');

if (adminMenuButton && adminSidebar && adminMenuClose && adminMenuBackdrop) {
    const compactLayout = window.matchMedia('(max-width: 980px)');

    const setAdminMenuOpen = (open, returnFocus = false) => {
        const isOpen = compactLayout.matches && open;
        const shouldHideSidebar = compactLayout.matches && !isOpen;

        adminSidebar.classList.toggle('is-open', isOpen);
        adminSidebar.setAttribute('aria-hidden', String(shouldHideSidebar));
        adminSidebar.inert = shouldHideSidebar;
        adminMenuButton.setAttribute('aria-expanded', String(isOpen));
        adminMenuButton.setAttribute('aria-label', isOpen ? 'Close admin menu' : 'Open admin menu');
        adminMenuBackdrop.hidden = !isOpen;
        document.body.classList.toggle('admin-menu-open', isOpen);

        if (isOpen) {
            adminMenuClose.focus();
        } else if (returnFocus) {
            adminMenuButton.focus();
        }
    };

    setAdminMenuOpen(false);

    adminMenuButton.addEventListener('click', () => {
        setAdminMenuOpen(adminMenuButton.getAttribute('aria-expanded') !== 'true');
    });
    adminMenuClose.addEventListener('click', () => setAdminMenuOpen(false, true));
    adminMenuBackdrop.addEventListener('click', () => setAdminMenuOpen(false, true));
    adminSidebar.querySelectorAll('nav a').forEach((link) => {
        link.addEventListener('click', () => setAdminMenuOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && adminMenuButton.getAttribute('aria-expanded') === 'true') {
            setAdminMenuOpen(false, true);
        }
    });
    compactLayout.addEventListener('change', () => setAdminMenuOpen(false));
}

document.querySelectorAll('[data-banner-slider]').forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll('[data-banner-slide]'));
    const dots = Array.from(slider.querySelectorAll('[data-banner-dot]'));
    const previousButton = slider.querySelector('[data-banner-prev]');
    const nextButton = slider.querySelector('[data-banner-next]');
    const toggleButton = slider.querySelector('[data-banner-toggle]');

    if (slides.length < 2) {
        return;
    }

    let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
    let timer;
    let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
        });

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', String(isActive));
        });
    };

    const stopRotation = () => {
        window.clearInterval(timer);
        timer = undefined;
    };

    const startRotation = () => {
        if (paused || timer) {
            return;
        }

        timer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
    };

    const restartRotation = () => {
        stopRotation();
        startRotation();
    };

    const updateToggleButton = () => {
        toggleButton.textContent = paused ? 'Play' : 'Pause';
        toggleButton.setAttribute(
            'aria-label',
            paused ? 'Start automatic banner rotation' : 'Pause automatic banner rotation',
        );
    };

    previousButton.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        restartRotation();
    });

    nextButton.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        restartRotation();
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.bannerDot));
            restartRotation();
        });
    });

    toggleButton.addEventListener('click', () => {
        paused = !paused;
        updateToggleButton();
        paused ? stopRotation() : startRotation();
    });

    slider.addEventListener('mouseenter', stopRotation);
    slider.addEventListener('mouseleave', startRotation);
    slider.addEventListener('focusin', stopRotation);
    slider.addEventListener('focusout', (event) => {
        if (!slider.contains(event.relatedTarget)) {
            startRotation();
        }
    });

    updateToggleButton();
    startRotation();
});
