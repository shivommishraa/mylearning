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

if (storeMenuButton && storeMenu) {
    const closeStoreSubmenus = () => {
        storeMenu.querySelectorAll('[data-nav-group].is-open').forEach((group) => {
            group.classList.remove('is-open');
            group.querySelector('[data-nav-submenu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const closeStoreMenu = () => {
        storeMenu.classList.remove('open');
        storeMenuButton.setAttribute('aria-expanded', 'false');
        storeMenuButton.setAttribute('aria-label', 'Open navigation');
        closeStoreSubmenus();
    };

    storeMenuButton.addEventListener('click', () => {
        const isOpen = storeMenu.classList.toggle('open');
        storeMenuButton.setAttribute('aria-expanded', String(isOpen));
        storeMenuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
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

    document.addEventListener('click', (event) => {
        if (!storeMenu.contains(event.target)) {
            closeStoreSubmenus();
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

document.querySelectorAll('[data-photo-editor]').forEach((editor) => {
    const input = editor.querySelector('[data-photo-input]');
    const preview = editor.querySelector('[data-photo-preview]');
    const backdrop = editor.querySelector('[data-crop-backdrop]');
    const canvas = editor.querySelector('[data-crop-canvas]');
    const zoomControl = editor.querySelector('[data-crop-zoom]');
    const status = editor.querySelector('[data-photo-status]');
    const errorMessage = editor.querySelector('[data-crop-error]');
    const removeCheckbox = editor.querySelector('[data-photo-remove]');
    const context = canvas?.getContext('2d');

    if (!input || !preview || !backdrop || !canvas || !zoomControl || !context) {
        return;
    }

    let image = null;
    let rotation = 0;
    let zoom = 1;
    let offsetX = 0;
    let offsetY = 0;
    let pointer = null;
    let committedFile = null;
    let previousFocus = null;

    const showError = (message) => {
        errorMessage.textContent = message;
        errorMessage.hidden = false;
        status.textContent = message;
        status.classList.add('is-error');
    };

    const clampOffset = (scale) => {
        const quarterTurn = Math.abs(rotation) % 180 === 90;
        const imageWidth = (quarterTurn ? image.height : image.width) * scale;
        const imageHeight = (quarterTurn ? image.width : image.height) * scale;
        const maxX = Math.max(0, (imageWidth - canvas.width) / 2);
        const maxY = Math.max(0, (imageHeight - canvas.height) / 2);
        offsetX = Math.max(-maxX, Math.min(maxX, offsetX));
        offsetY = Math.max(-maxY, Math.min(maxY, offsetY));
    };

    const drawCrop = () => {
        if (!image) {
            return;
        }

        const quarterTurn = Math.abs(rotation) % 180 === 90;
        const baseWidth = quarterTurn ? image.height : image.width;
        const baseHeight = quarterTurn ? image.width : image.height;
        const scale = Math.max(canvas.width / baseWidth, canvas.height / baseHeight) * zoom;
        clampOffset(scale);

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.save();
        context.beginPath();
        context.rect(0, 0, canvas.width, canvas.height);
        context.clip();
        context.translate(canvas.width / 2 + offsetX, canvas.height / 2 + offsetY);
        context.rotate((rotation * Math.PI) / 180);
        context.drawImage(image, (-image.width * scale) / 2, (-image.height * scale) / 2, image.width * scale, image.height * scale);
        context.restore();
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
            zoom = 1;
            offsetX = 0;
            offsetY = 0;
            zoomControl.value = '1';
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

    zoomControl.addEventListener('input', () => {
        zoom = Number(zoomControl.value);
        drawCrop();
    });

    editor.querySelectorAll('[data-crop-rotate]').forEach((button) => {
        button.addEventListener('click', () => {
            rotation = (rotation + Number(button.dataset.cropRotate) + 360) % 360;
            offsetX = 0;
            offsetY = 0;
            drawCrop();
        });
    });

    canvas.addEventListener('pointerdown', (event) => {
        if (!image) {
            return;
        }

        const bounds = canvas.getBoundingClientRect();
        pointer = {
            id: event.pointerId,
            x: event.clientX,
            y: event.clientY,
            offsetX,
            offsetY,
            scaleX: canvas.width / bounds.width,
            scaleY: canvas.height / bounds.height,
        };
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!pointer || pointer.id !== event.pointerId) {
            return;
        }

        offsetX = pointer.offsetX + (event.clientX - pointer.x) * pointer.scaleX;
        offsetY = pointer.offsetY + (event.clientY - pointer.y) * pointer.scaleY;
        drawCrop();
    });

    canvas.addEventListener('pointerup', (event) => {
        if (pointer?.id === event.pointerId) {
            pointer = null;
        }
    });

    editor.querySelectorAll('[data-crop-cancel]').forEach((button) => {
        button.addEventListener('click', () => closeEditor(true));
    });

    backdrop.addEventListener('click', (event) => {
        if (event.target === backdrop) {
            closeEditor(true);
        }
    });

    editor.querySelector('[data-crop-apply]')?.addEventListener('click', () => {
        canvas.toBlob((blob) => {
            if (!blob) {
                showError('The cropped photo could not be created. Please try another image.');
                return;
            }

            const file = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
            setInputFile(file);
            committedFile = file;
            updatePreview(canvas.toDataURL('image/jpeg', 0.9));
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
