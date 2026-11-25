import './bootstrap';
import '../css/responsive.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const pageLoader = document.querySelector('[data-page-loader]');
if (pageLoader) {
    let dismissed = false;
    let fallbackTimer;
    const dismissLoader = () => {
        if (dismissed) {
            return;
        }

        dismissed = true;
        window.clearTimeout(fallbackTimer);
        pageLoader.classList.add('is-hidden');
        pageLoader.setAttribute('aria-hidden', 'true');
        window.setTimeout(() => pageLoader.remove(), 450);
    };

    fallbackTimer = window.setTimeout(dismissLoader, 8000);
    if (document.readyState === 'complete') {
        window.setTimeout(dismissLoader, 120);
    } else {
        window.addEventListener('load', dismissLoader, { once: true });
    }
}

const backToTopButton = document.querySelector('[data-back-to-top]');
if (backToTopButton) {
    let updateScheduled = false;
    const updateVisibility = () => {
        if (updateScheduled) {
            return;
        }

        updateScheduled = true;
        window.requestAnimationFrame(() => {
            const isVisible = window.scrollY > 420;
            backToTopButton.classList.toggle('is-visible', isVisible);
            backToTopButton.setAttribute('aria-hidden', String(!isVisible));
            backToTopButton.tabIndex = isVisible ? 0 : -1;
            updateScheduled = false;
        });
    };

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();
    backToTopButton.addEventListener('click', () => {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
        backToTopButton.blur();
    });
}

const liveSearchForm = document.querySelector('[data-live-search]');

if (liveSearchForm) {
    const searchInput = liveSearchForm.querySelector('[data-search-input]');
    const searchPanel = liveSearchForm.querySelector('[data-search-panel]');
    const searchMessage = liveSearchForm.querySelector('[data-search-message]');
    const searchResults = liveSearchForm.querySelector('[data-search-results]');
    const searchAllLink = liveSearchForm.querySelector('[data-search-all]');
    let searchTimer;
    let searchRequest;
    let activeSearchOption = -1;

    const setPanelOpen = (open) => {
        searchPanel.hidden = !open;
        searchInput.setAttribute('aria-expanded', String(open));
        activeSearchOption = -1;
        searchInput.removeAttribute('aria-activedescendant');
    };

    const makeSearchUrl = (term) => {
        const url = new URL(liveSearchForm.action, window.location.origin);
        url.searchParams.set('q', term);

        return url.toString();
    };

    const showSearchMessage = (text, searchUrl = null) => {
        searchResults.replaceChildren();
        searchMessage.textContent = text;
        searchMessage.hidden = false;
        searchAllLink.hidden = !searchUrl;

        if (searchUrl) {
            searchAllLink.href = searchUrl;
            searchAllLink.textContent = `Search all products for “${searchInput.value.trim()}”`;
        }

        setPanelOpen(true);
    };

    const renderSearchResults = (products, searchUrl) => {
        searchResults.replaceChildren();
        searchMessage.hidden = true;

        products.forEach((product, index) => {
            const option = document.createElement('a');
            option.className = 'search-suggestion';
            option.href = product.url;
            option.id = `search-suggestion-${index}`;
            option.dataset.searchOption = '';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');

            const imageWrap = document.createElement('span');
            imageWrap.className = 'search-suggestion-image';
            imageWrap.setAttribute('aria-hidden', 'true');

            if (product.image_url) {
                const image = document.createElement('img');
                image.src = product.image_url;
                image.alt = '';
                image.loading = 'lazy';
                image.addEventListener('error', () => {
                    image.remove();
                    imageWrap.textContent = product.name.slice(0, 1).toUpperCase();
                }, { once: true });
                imageWrap.append(image);
            } else {
                imageWrap.textContent = product.name.slice(0, 1).toUpperCase();
            }

            const copy = document.createElement('span');
            copy.className = 'search-suggestion-copy';
            const name = document.createElement('span');
            name.className = 'search-suggestion-name';
            name.textContent = product.name;
            const meta = document.createElement('span');
            meta.className = 'search-suggestion-meta';
            meta.textContent = product.meta || 'Everyday essential';
            copy.append(name, meta);
            option.append(imageWrap, copy);
            searchResults.append(option);
        });

        searchAllLink.href = searchUrl;
        searchAllLink.textContent = `View all results for “${searchInput.value.trim()}”`;
        searchAllLink.hidden = false;
        setPanelOpen(true);
    };

    const fetchSearchResults = (term) => {
        searchRequest?.abort();
        searchRequest = new AbortController();

        const url = new URL(liveSearchForm.dataset.suggestionsUrl, window.location.origin);
        url.searchParams.set('q', term);

        fetch(url, {
            headers: { Accept: 'application/json' },
            signal: searchRequest.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Search is temporarily unavailable.');
                }

                return response.json();
            })
            .then((result) => {
                if (term !== searchInput.value.trim()) {
                    return;
                }

                if (result.products.length) {
                    renderSearchResults(result.products, result.search_url);
                } else {
                    showSearchMessage('No products found. Try another search.', result.search_url);
                }
            })
            .catch((error) => {
                if (error.name !== 'AbortError' && term === searchInput.value.trim()) {
                    showSearchMessage(error.message || 'Search is temporarily unavailable.', makeSearchUrl(term));
                }
            })
            .finally(() => {
                if (term === searchInput.value.trim()) {
                    searchInput.removeAttribute('aria-busy');
                }
            });
    };

    searchInput.addEventListener('input', () => {
        const term = searchInput.value.trim();
        window.clearTimeout(searchTimer);
        searchRequest?.abort();
        searchInput.removeAttribute('aria-busy');

        if (!term) {
            setPanelOpen(false);

            return;
        }

        if (term.length < 2) {
            searchAllLink.hidden = true;
            showSearchMessage('Type at least 2 characters to search.');

            return;
        }

        searchAllLink.hidden = true;
        showSearchMessage('Searching products…');
        searchInput.setAttribute('aria-busy', 'true');
        searchTimer = window.setTimeout(() => fetchSearchResults(term), 220);
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 2 && searchPanel.hidden) {
            searchInput.dispatchEvent(new Event('input'));
        }
    });

    searchInput.addEventListener('keydown', (event) => {
        const options = Array.from(searchResults.querySelectorAll('[data-search-option]'));

        if (event.key === 'ArrowDown' && options.length) {
            event.preventDefault();
            activeSearchOption = (activeSearchOption + 1) % options.length;
        } else if (event.key === 'ArrowUp' && options.length) {
            event.preventDefault();
            activeSearchOption = activeSearchOption <= 0 ? options.length - 1 : activeSearchOption - 1;
        } else if (event.key === 'Escape') {
            setPanelOpen(false);

            return;
        } else if (event.key === 'Enter' && activeSearchOption >= 0 && options[activeSearchOption]) {
            event.preventDefault();
            window.location.assign(options[activeSearchOption].href);

            return;
        } else {
            return;
        }

        options.forEach((option, index) => {
            const isActive = index === activeSearchOption;
            option.classList.toggle('is-active', isActive);
            option.setAttribute('aria-selected', String(isActive));
        });
        searchInput.setAttribute('aria-activedescendant', options[activeSearchOption].id);
        options[activeSearchOption].scrollIntoView({ block: 'nearest' });
    });

    document.addEventListener('pointerdown', (event) => {
        if (!liveSearchForm.contains(event.target)) {
            setPanelOpen(false);
        }
    });
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-product-action]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector('button');
    const productContainer = form.closest('.product-card, .product-detail');
    const feedback = form.querySelector('[data-product-feedback]')
        || productContainer?.querySelector('[data-product-feedback]');
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
            const productName = form.dataset.productName
                || productContainer?.querySelector('.product-title, h1')?.textContent.trim()
                || 'product';
            button.classList.toggle('is-saved', isWishlisted);
            button.setAttribute('aria-pressed', String(isWishlisted));
            button.setAttribute('title', isWishlisted ? 'Remove from wishlist' : 'Save to wishlist');
            button.setAttribute(
                'aria-label',
                `${isWishlisted ? 'Remove' : 'Add'} ${productName} ${isWishlisted ? 'from' : 'to'} wishlist`,
            );
            const detailLabel = button.querySelector('span + span');
            if (detailLabel) {
                detailLabel.textContent = isWishlisted ? 'Saved to wishlist' : 'Add to wishlist';
            }
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
const storeMenuBackdrop = document.querySelector('[data-menu-backdrop]');

if (storeMenuButton && storeMenu) {
    const storeHeader = storeMenu.closest('.header');

    const closeStoreSubmenus = () => {
        storeMenu.querySelectorAll('[data-nav-group].is-open').forEach((group) => {
            group.classList.remove('is-open');
            group.querySelector('[data-nav-submenu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const updateStoreMenuBackdrop = () => {
        if (!storeMenuBackdrop || !storeHeader || !storeMenu.classList.contains('open')) {
            return;
        }

        const headerRect = storeHeader.getBoundingClientRect();
        const top = Math.max(0, headerRect.top);
        const menuHeight = Math.min(
            storeMenu.scrollHeight,
            Math.max(0, window.innerHeight - 130),
            Math.max(0, window.innerHeight - headerRect.bottom),
        );

        storeMenuBackdrop.style.top = `${top}px`;
        storeMenuBackdrop.style.height = `${Math.max(0, headerRect.bottom + menuHeight + 2 - top)}px`;
    };

    const closeStoreMenu = () => {
        storeMenu.classList.remove('open');
        storeMenuButton.setAttribute('aria-expanded', 'false');
        storeMenuButton.setAttribute('aria-label', 'Open navigation');
        storeHeader?.classList.remove('menu-open');
        if (storeMenuBackdrop) {
            storeMenuBackdrop.hidden = true;
        }
        closeStoreSubmenus();
    };

    storeMenuButton.addEventListener('click', () => {
        const isOpen = storeMenu.classList.toggle('open');
        storeMenuButton.setAttribute('aria-expanded', String(isOpen));
        storeMenuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
        storeHeader?.classList.toggle('menu-open', isOpen);
        if (storeMenuBackdrop) {
            storeMenuBackdrop.hidden = !isOpen;
            if (isOpen) {
                updateStoreMenuBackdrop();
                requestAnimationFrame(updateStoreMenuBackdrop);
            }
        }
    });

    storeMenuBackdrop?.addEventListener('click', () => {
        closeStoreMenu();
        storeMenuButton.focus();
    });

    storeMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeStoreMenu);
    });

    storeMenu.querySelectorAll('[data-nav-submenu-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const group = toggle.closest('[data-nav-group]');
            const shouldOpen = !group.classList.contains('is-open');
            closeStoreSubmenus();

            if (shouldOpen) {
                group.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });

    storeMenu.addEventListener('transitionend', (event) => {
        if (event.propertyName === 'max-height') {
            updateStoreMenuBackdrop();
        }
    });

    window.addEventListener('resize', updateStoreMenuBackdrop);
    window.addEventListener('scroll', updateStoreMenuBackdrop, { passive: true });

    document.addEventListener('click', (event) => {
        if (!storeMenu.contains(event.target)) {
            closeStoreSubmenus();

            if (storeMenu.classList.contains('open') && !storeMenuButton.contains(event.target)) {
                closeStoreMenu();
            }
        }
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

document.querySelectorAll('[data-testimonial-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-testimonial-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-testimonial-dot]'));
    const previousButton = carousel.querySelector('[data-testimonial-prev]');
    const nextButton = carousel.querySelector('[data-testimonial-next]');

    if (slides.length < 2) {
        return;
    }

    let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));

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

    previousButton.addEventListener('click', () => showSlide(activeIndex - 1));
    nextButton.addEventListener('click', () => showSlide(activeIndex + 1));
    dots.forEach((dot) => {
        dot.addEventListener('click', () => showSlide(Number(dot.dataset.testimonialDot)));
    });

    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showSlide(activeIndex - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            showSlide(activeIndex + 1);
        }
    });

    let touchStartX = null;
    carousel.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0].clientX;
    }, { passive: true });
    carousel.addEventListener('touchend', (event) => {
        if (touchStartX === null) {
            return;
        }

        const swipeDistance = event.changedTouches[0].clientX - touchStartX;
        touchStartX = null;

        if (Math.abs(swipeDistance) >= 45) {
            showSlide(activeIndex + (swipeDistance < 0 ? 1 : -1));
        }
    }, { passive: true });
});

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
        toggleButton.textContent = paused ? '▶' : 'Ⅱ';
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

document.querySelectorAll('[data-welcome-popup]').forEach((popup) => {
    const dialog = popup.querySelector('[role="dialog"]');
    const frequency = popup.dataset.displayFrequency;
    const storageKey = `ssg-welcome-popup-${popup.dataset.popupId}-${popup.dataset.popupVersion}`;
    let dismissalStorage = null;
    let alreadyShown = false;

    try {
        dismissalStorage = frequency === 'once_per_session'
            ? window.sessionStorage
            : frequency === 'once_per_visitor'
                ? window.localStorage
                : null;
        alreadyShown = dismissalStorage?.getItem(storageKey) === 'shown';
    } catch (error) {
        if (!(error instanceof DOMException)) {
            throw error;
        }
        dismissalStorage = null;
    }

    if (alreadyShown) {
        return;
    }

    const closeButton = popup.querySelector('[data-welcome-close]');
    const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let previousFocus;
    let showTimer;

    const closePopup = () => {
        window.clearTimeout(showTimer);
        popup.hidden = true;
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('welcome-popup-open');
        document.removeEventListener('keydown', handleKeydown);
        previousFocus?.focus();
    };

    const handleKeydown = (event) => {
        if (event.key === 'Escape') {
            closePopup();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = Array.from(dialog.querySelectorAll(focusableSelector));
        if (focusable.length === 0) {
            event.preventDefault();
            dialog.focus();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    const openPopup = () => {
        previousFocus = document.activeElement;
        popup.hidden = false;
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('welcome-popup-open');
        document.addEventListener('keydown', handleKeydown);
        (popup.querySelector(focusableSelector) || dialog).focus();
        try {
            dismissalStorage?.setItem(storageKey, 'shown');
        } catch (error) {
            if (!(error instanceof DOMException)) {
                throw error;
            }
        }
    };

    popup.addEventListener('click', (event) => {
        if (event.target === popup) {
            closePopup();
        }
    });

    closeButton?.addEventListener('click', closePopup);

    const images = Array.from(popup.querySelectorAll('[data-welcome-image]'));
    const imageDots = Array.from(popup.querySelectorAll('[data-welcome-image-dot]'));
    if (images.length > 1) {
        let activeImage = 0;
        const showImage = (index) => {
            activeImage = (index + images.length) % images.length;
            images.forEach((image, imageIndex) => {
                const isActive = imageIndex === activeImage;
                image.classList.toggle('is-active', isActive);
                image.setAttribute('aria-hidden', String(!isActive));
            });
            imageDots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === activeImage;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-current', String(isActive));
            });
        };

        popup.querySelector('[data-welcome-image-prev]')?.addEventListener('click', () => showImage(activeImage - 1));
        popup.querySelector('[data-welcome-image-next]')?.addEventListener('click', () => showImage(activeImage + 1));
        imageDots.forEach((dot) => {
            dot.addEventListener('click', () => showImage(Number(dot.dataset.welcomeImageDot)));
        });
    }

    showTimer = window.setTimeout(openPopup, Number(popup.dataset.displayDelay || 0) * 1000);
});

document.querySelectorAll('[data-contact-pair]').forEach((form) => {
    const email = form.querySelector('[data-contact-email]');
    const phone = form.querySelector('[data-contact-phone]');

    if (!email || !phone) {
        return;
    }

    const syncRequiredFields = () => {
        const hasEmail = email.value.trim() !== '';
        const hasPhone = phone.value.trim() !== '';
        email.required = !hasPhone;
        phone.required = !hasEmail;
    };

    email.addEventListener('input', syncRequiredFields);
    phone.addEventListener('input', syncRequiredFields);
    syncRequiredFields();
});

document.querySelectorAll('[data-validated-form]').forEach((form) => {
    const fields = [...form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="file"]):not([type="submit"]):not([type="button"]), select, textarea')];
    const email = form.querySelector('[data-contact-email]');
    const phone = form.querySelector('[data-contact-phone]');
    const password = form.querySelector('[data-password-field]');
    const confirmation = form.querySelector('[data-password-confirmation]');
    const serverErrors = new WeakMap();
    const clientMessages = new WeakMap();
    let submitted = false;

    const wrapperFor = (field) => field.closest('.auth-form-field, .customer-profile-field') || field.parentElement;
    const serverErrorFor = (field) => {
        const wrapper = wrapperFor(field);
        const existing = wrapper?.querySelector('.field-error, .text-red-600');

        return existing && !existing.hasAttribute('data-client-validation-message') && existing.textContent.trim()
            ? existing
            : null;
    };

    const clearServerError = (field) => {
        const serverError = serverErrors.get(field);
        if (serverError && (field.value !== field.dataset.validationInitialValue || field.dataset.contactValueChanged === 'true')) {
            serverError.remove();
            serverErrors.delete(field);
        }
    };

    const clientMessageFor = (field) => {
        let message = clientMessages.get(field);
        if (!message) {
            message = document.createElement('p');
            message.className = 'field-error client-validation-message';
            message.dataset.clientValidationMessage = '';
            wrapperFor(field)?.append(message);
            clientMessages.set(field, message);
        }

        return message;
    };

    const messageFor = (field) => {
        if ((field === email || field === phone) && !email?.value.trim() && !phone?.value.trim()) {
            return 'Enter an email address or mobile number.';
        }
        if (field.validity.valueMissing) {
            const label = wrapperFor(field)?.querySelector('label')?.textContent.replace(/\s+/g, ' ').trim();
            return `${label || 'This field'} is required.`;
        }
        if (field.validity.typeMismatch && field.type === 'email') {
            return 'Enter a valid email address.';
        }

        return field.validationMessage || 'Check this field and try again.';
    };

    const updateField = (field, force = false) => {
        if (field.disabled) {
            return true;
        }

        clearServerError(field);
        const serverError = serverErrors.get(field);
        const hasValue = field.value.trim() !== '';
        const shouldValidate = Boolean(serverError) || ((force || submitted || field.dataset.validationTouched === 'true') && (field.required || hasValue));
        if (!shouldValidate) {
            field.classList.remove('is-invalid', 'is-valid');
            field.removeAttribute('aria-invalid');
            clientMessages.get(field)?.remove();
            clientMessages.delete(field);
            return true;
        }

        const valid = field.validity.valid && !serverError;
        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid);
        field.setAttribute('aria-invalid', String(!valid));

        const clientMessage = clientMessages.get(field);
        if (!valid && !serverError) {
            const message = clientMessageFor(field);
            message.textContent = messageFor(field);
        } else {
            clientMessage?.remove();
            clientMessages.delete(field);
        }

        return valid;
    };

    const syncPasswordConfirmation = () => {
        if (password && confirmation) {
            confirmation.setCustomValidity(
                confirmation.value && confirmation.value !== password.value ? 'Passwords do not match.' : '',
            );
        }
    };

    fields.forEach((field) => {
        field.dataset.validationInitialValue = field.value;
        const serverError = serverErrorFor(field);
        if (serverError) {
            serverErrors.set(field, serverError);
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
        }

        field.addEventListener('input', () => {
            field.dataset.validationTouched = 'true';
            if (field === email || field === phone) {
                field.dataset.contactValueChanged = 'true';
                clearServerError(field);
                const otherContact = field === email ? phone : email;
                if (otherContact && !otherContact.value.trim()) {
                    otherContact.dataset.contactValueChanged = 'true';
                    clearServerError(otherContact);
                }
            } else {
                clearServerError(field);
            }

            syncPasswordConfirmation();
            updateField(field);
            if ((field === email || field === phone) && email && phone) {
                updateField(field === email ? phone : email);
            }
            if (field === password && confirmation) {
                updateField(confirmation);
            }
        });

        field.addEventListener('blur', () => {
            field.dataset.validationTouched = 'true';
            syncPasswordConfirmation();
            updateField(field);
        });
    });

    form.addEventListener('invalid', (event) => {
        submitted = true;
        if (fields.includes(event.target)) {
            updateField(event.target, true);
        }
    }, true);

    form.addEventListener('submit', (event) => {
        submitted = true;
        syncPasswordConfirmation();
        const invalidFields = fields.filter((field) => !updateField(field, true));

        if (invalidFields.length) {
            event.preventDefault();
            invalidFields[0].focus();
        }
    });
});

document.querySelectorAll('[data-additional-details-toggle]').forEach((toggle) => {
    const section = toggle.closest('[data-additional-details]');
    const panel = section?.querySelector('[data-additional-details-panel]');

    if (!panel) {
        return;
    }

    const syncAdditionalDetails = () => {
        panel.hidden = !toggle.checked;
        panel.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !toggle.checked;
        });
    };

    toggle.addEventListener('change', syncAdditionalDetails);
    syncAdditionalDetails();
});

document.querySelectorAll('[data-photo-editor]').forEach((editor) => {
    const input = editor.querySelector('[data-photo-input]');
    const preview = editor.querySelector('[data-photo-preview]');
    const backdrop = editor.querySelector('[data-crop-backdrop]');
    const canvas = editor.querySelector('[data-crop-canvas]');
    const status = editor.querySelector('[data-photo-status]');
    const errorMessage = editor.querySelector('[data-crop-error]');
    const removeCheckbox = editor.querySelector('[data-photo-remove]');
    const context = canvas?.getContext('2d');

    if (!input || !preview || !backdrop || !canvas || !context) {
        return;
    }

    let image = null;
    let rotation = 0;
    let imageRect = null;
    let selection = null;
    let pointer = null;
    let committedFile = null;
    let previousFocus = null;
    const outputSize = 320;

    const showError = (message) => {
        errorMessage.textContent = message;
        errorMessage.hidden = false;
        status.textContent = message;
        status.classList.add('is-error');
    };

    const getImageRect = () => {
        const quarterTurn = Math.abs(rotation) % 180 === 90;
        const baseWidth = quarterTurn ? image.height : image.width;
        const baseHeight = quarterTurn ? image.width : image.height;
        const scale = Math.min(canvas.width / baseWidth, canvas.height / baseHeight);
        const width = baseWidth * scale;
        const height = baseHeight * scale;

        return {
            x: (canvas.width - width) / 2,
            y: (canvas.height - height) / 2,
            width,
            height,
            scale,
            baseWidth,
            baseHeight,
        };
    };

    const resetSelection = () => {
        const size = Math.min(imageRect.width, imageRect.height);
        selection = {
            x: imageRect.x + (imageRect.width - size) / 2,
            y: imageRect.y + (imageRect.height - size) / 2,
            size,
        };
    };

    const drawCrop = () => {
        if (!image) {
            return;
        }

        imageRect = getImageRect();

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.save();
        context.translate(canvas.width / 2, canvas.height / 2);
        context.rotate((rotation * Math.PI) / 180);
        context.drawImage(
            image,
            (-image.width * imageRect.scale) / 2,
            (-image.height * imageRect.scale) / 2,
            image.width * imageRect.scale,
            image.height * imageRect.scale,
        );
        context.restore();

        if (!selection) {
            resetSelection();
        }

        const right = selection.x + selection.size;
        const bottom = selection.y + selection.size;
        context.fillStyle = 'rgba(12, 24, 16, 0.58)';
        context.fillRect(imageRect.x, imageRect.y, imageRect.width, selection.y - imageRect.y);
        context.fillRect(imageRect.x, bottom, imageRect.width, imageRect.y + imageRect.height - bottom);
        context.fillRect(imageRect.x, selection.y, selection.x - imageRect.x, selection.size);
        context.fillRect(right, selection.y, imageRect.x + imageRect.width - right, selection.size);
        context.strokeStyle = '#ffffff';
        context.lineWidth = 2;
        context.strokeRect(selection.x + 1, selection.y + 1, selection.size - 2, selection.size - 2);
        context.fillStyle = '#ffffff';
        context.fillRect(right - 7, bottom - 7, 14, 14);
        context.strokeStyle = '#236b45';
        context.lineWidth = 2;
        context.strokeRect(right - 7, bottom - 7, 14, 14);
    };

    const setInputFile = (file) => {
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    };

    const closeEditor = (restoreFile) => {
        backdrop.hidden = true;
        document.body.classList.remove('photo-crop-open');
        image = null;
        imageRect = null;
        selection = null;
        pointer = null;

        if (restoreFile) {
            if (committedFile) {
                setInputFile(committedFile);
            } else {
                input.value = '';
            }
        }

        previousFocus?.focus();
    };

    const openEditor = (file) => {
        const objectUrl = URL.createObjectURL(file);
        const source = new Image();

        source.onload = () => {
            URL.revokeObjectURL(objectUrl);
            image = source;
            rotation = 0;
            selection = null;
            errorMessage.hidden = true;
            errorMessage.textContent = '';
            previousFocus = document.activeElement;
            backdrop.hidden = false;
            document.body.classList.add('photo-crop-open');
            drawCrop();
            editor.querySelector('[data-crop-apply]')?.focus();
        };

        source.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            if (committedFile) {
                setInputFile(committedFile);
            } else {
                input.value = '';
            }
            showError('This image could not be opened. Please choose another photo.');
        };

        source.src = objectUrl;
    };

    const openCurrentPhoto = async () => {
        const stagedFile = committedFile || input.files?.[0];
        if (stagedFile) {
            openEditor(stagedFile);
            return;
        }

        const imageUrl = preview.querySelector('[data-photo-preview-image]')?.src;
        if (!imageUrl) {
            input.click();
            return;
        }

        status.textContent = 'Loading saved photo...';
        status.classList.remove('is-error');
        try {
            const response = await fetch(imageUrl, { credentials: 'same-origin' });
            if (!response.ok) {
                throw new Error('Photo request failed.');
            }

            const blob = await response.blob();
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(blob.type)) {
                throw new Error('Photo response was not a supported image.');
            }
            const file = new File([blob], 'profile-photo.jpg', { type: blob.type || 'image/jpeg' });
            status.textContent = '';
            openEditor(file);
        } catch {
            showError('The saved photo could not be opened. Please choose another photo.');
        }
    };

    const updatePreview = (dataUrl) => {
        let previewImage = preview.querySelector('[data-photo-preview-image]');
        if (!previewImage) {
            previewImage = document.createElement('img');
            previewImage.dataset.photoPreviewImage = '';
            previewImage.alt = 'Profile photo preview';
            preview.querySelector('[data-photo-placeholder]')?.remove();
            preview.append(previewImage);
        }

        previewImage.src = dataUrl;
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            if (committedFile) {
                setInputFile(committedFile);
            } else {
                input.value = '';
            }
            showError('Choose a JPG, PNG or WebP image up to 5 MB.');
            return;
        }

        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }

        status.textContent = '';
        status.classList.remove('is-error');
        openEditor(file);
    });

    editor.querySelectorAll('[data-crop-rotate]').forEach((button) => {
        button.addEventListener('click', () => {
            rotation = (rotation + Number(button.dataset.cropRotate) + 360) % 360;
            selection = null;
            drawCrop();
        });
    });

    const getCanvasPoint = (event) => {
        const bounds = canvas.getBoundingClientRect();
        return {
            x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
            y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
        };
    };

    const clampPoint = (point) => ({
        x: Math.max(imageRect.x, Math.min(imageRect.x + imageRect.width, point.x)),
        y: Math.max(imageRect.y, Math.min(imageRect.y + imageRect.height, point.y)),
    });

    canvas.addEventListener('pointerdown', (event) => {
        if (!image || !imageRect || !selection) {
            return;
        }

        const point = clampPoint(getCanvasPoint(event));
        const right = selection.x + selection.size;
        const bottom = selection.y + selection.size;
        const nearHandle = Math.abs(point.x - right) <= 18 && Math.abs(point.y - bottom) <= 18;
        const inSelection = point.x >= selection.x && point.x <= right && point.y >= selection.y && point.y <= bottom;
        pointer = {
            id: event.pointerId,
            mode: nearHandle ? 'resize' : inSelection ? 'move' : 'draw',
            startX: point.x,
            startY: point.y,
            startSelection: { ...selection },
        };
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!pointer || pointer.id !== event.pointerId) {
            return;
        }

        const point = clampPoint(getCanvasPoint(event));
        const deltaX = point.x - pointer.startX;
        const deltaY = point.y - pointer.startY;
        const original = pointer.startSelection;

        if (pointer.mode === 'move') {
            selection = {
                ...original,
                x: Math.max(imageRect.x, Math.min(imageRect.x + imageRect.width - original.size, original.x + deltaX)),
                y: Math.max(imageRect.y, Math.min(imageRect.y + imageRect.height - original.size, original.y + deltaY)),
            };
        } else if (pointer.mode === 'resize') {
            const maxSize = Math.min(imageRect.x + imageRect.width - original.x, imageRect.y + imageRect.height - original.y);
            selection = {
                ...original,
                size: Math.max(Math.min(32, maxSize), Math.min(maxSize, original.size + ((deltaX + deltaY) / 2))),
            };
        } else {
            const signX = deltaX < 0 ? -1 : 1;
            const signY = deltaY < 0 ? -1 : 1;
            const maxX = signX < 0 ? pointer.startX - imageRect.x : imageRect.x + imageRect.width - pointer.startX;
            const maxY = signY < 0 ? pointer.startY - imageRect.y : imageRect.y + imageRect.height - pointer.startY;
            const size = Math.min(Math.max(Math.abs(deltaX), Math.abs(deltaY)), maxX, maxY);
            selection = {
                x: signX < 0 ? pointer.startX - size : pointer.startX,
                y: signY < 0 ? pointer.startY - size : pointer.startY,
                size,
            };
        }

        drawCrop();
    });

    canvas.addEventListener('pointerup', (event) => {
        if (pointer?.id === event.pointerId) {
            if (pointer.mode === 'draw' && selection.size < 32) {
                selection = pointer.startSelection;
                drawCrop();
            }
            pointer = null;
        }
    });
    canvas.addEventListener('pointercancel', () => {
        pointer = null;
    });

    editor.querySelectorAll('[data-crop-cancel]').forEach((button) => {
        button.addEventListener('click', () => closeEditor(true));
    });

    backdrop.addEventListener('click', (event) => {
        if (event.target === backdrop) {
            closeEditor(true);
        }
    });

    editor.addEventListener('open-current-photo', openCurrentPhoto);

    editor.querySelector('[data-crop-apply]')?.addEventListener('click', () => {
        if (!image || !imageRect || !selection || selection.size <= 0) {
            showError('Select an area of the photo before saving.');
            return;
        }

        const croppedCanvas = document.createElement('canvas');
        croppedCanvas.width = outputSize;
        croppedCanvas.height = outputSize;
        const croppedContext = croppedCanvas.getContext('2d');
        if (!croppedContext) {
            showError('The photo could not be cropped. Please try another image.');
            return;
        }

        const cropX = (selection.x - imageRect.x) / imageRect.scale;
        const cropY = (selection.y - imageRect.y) / imageRect.scale;
        const cropSize = selection.size / imageRect.scale;
        const outputScale = outputSize / cropSize;
        croppedContext.setTransform(outputScale, 0, 0, outputScale, -cropX * outputScale, -cropY * outputScale);
        croppedContext.translate(imageRect.baseWidth / 2, imageRect.baseHeight / 2);
        croppedContext.rotate((rotation * Math.PI) / 180);
        croppedContext.drawImage(image, -image.width / 2, -image.height / 2);

        croppedCanvas.toBlob((blob) => {
            if (!blob) {
                showError('The cropped photo could not be created. Please try another image.');
                return;
            }

            const file = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
            setInputFile(file);
            committedFile = file;
            updatePreview(croppedCanvas.toDataURL('image/jpeg', 0.9));
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
            status.textContent = 'Photo cropped and ready to save.';
            status.classList.remove('is-error');
            closeEditor(false);
        }, 'image/jpeg', 0.9);
    });

    backdrop.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeEditor(true);
        }
    });
});

document.addEventListener('click', (event) => {
    const trigger = event.target instanceof Element ? event.target.closest('[data-photo-open]') : null;
    if (!trigger) {
        return;
    }

    const editor = trigger.closest('[data-photo-editor]') || document.querySelector('[data-photo-editor]');
    editor?.dispatchEvent(new Event('open-current-photo'));
});

const photoLightbox = document.querySelector('[data-photo-lightbox]');
const photoLightboxTrigger = document.querySelector('[data-photo-lightbox-trigger]');

if (photoLightbox && photoLightboxTrigger) {
    const closeButton = photoLightbox.querySelector('[data-photo-lightbox-close]');
    let previousFocus = null;

    const closePhotoLightbox = () => {
        photoLightbox.hidden = true;
        document.body.classList.remove('photo-lightbox-open');
        previousFocus?.focus();
    };

    photoLightboxTrigger.addEventListener('click', () => {
        previousFocus = photoLightboxTrigger;
        photoLightbox.hidden = false;
        document.body.classList.add('photo-lightbox-open');
        closeButton?.focus();
    });

    closeButton?.addEventListener('click', closePhotoLightbox);
    photoLightbox.addEventListener('click', (event) => {
        if (event.target === photoLightbox) {
            closePhotoLightbox();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !photoLightbox.hidden) {
            closePhotoLightbox();
        }
    });
}
