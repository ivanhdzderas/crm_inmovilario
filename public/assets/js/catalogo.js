document.addEventListener('DOMContentLoaded', () => {
    initCatalogoCarousels();
    initCatalogoLightbox();
    initLegacyGallery();
});

function initCatalogoCarousels() {
    document.querySelectorAll('.catalogo-carousel').forEach((carouselEl) => {
        const total = carouselEl.querySelectorAll('.carousel-item').length;
        const counter = carouselEl.querySelector('[data-carousel-counter]');
        const wrap = carouselEl.closest('.catalogo-carousel-wrap');
        const thumbs = wrap?.querySelectorAll('.catalogo-thumb-btn') ?? [];

        const syncUi = (index) => {
            if (counter && total > 0) {
                counter.textContent = `${index + 1} / ${total}`;
            }
            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        };

        carouselEl.addEventListener('slid.bs.carousel', (event) => {
            syncUi(event.to);
        });

        syncUi(0);
    });
}

function initCatalogoLightbox() {
    const lightbox = createLightboxElement();
    let sources = [];
    let current = 0;
    let scale = 1;
    let translateX = 0;
    let translateY = 0;
    let pinchStartDistance = 0;
    let pinchStartScale = 1;
    let panStartX = 0;
    let panStartY = 0;
    let panOriginX = 0;
    let panOriginY = 0;
    let lastTap = 0;
    let isPanning = false;

    const stage = lightbox.querySelector('.catalogo-lightbox-stage');
    const img = lightbox.querySelector('.catalogo-lightbox-img');
    const counter = lightbox.querySelector('.catalogo-lightbox-counter');
    const closeBtn = lightbox.querySelector('.catalogo-lightbox-close');
    const prevBtn = lightbox.querySelector('.catalogo-lightbox-prev');
    const nextBtn = lightbox.querySelector('.catalogo-lightbox-next');

    const resetTransform = () => {
        scale = 1;
        translateX = 0;
        translateY = 0;
        applyTransform();
        lightbox.classList.remove('is-zoomed');
    };

    const applyTransform = () => {
        img.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
        lightbox.classList.toggle('is-zoomed', scale > 1.02);
    };

    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    const getTouchDistance = (touches) => {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.hypot(dx, dy);
    };

    const showImage = (index) => {
        if (!sources.length) {
            return;
        }
        current = (index + sources.length) % sources.length;
        img.src = sources[current];
        resetTransform();
        counter.textContent = `${current + 1} / ${sources.length}`;
        prevBtn.hidden = sources.length <= 1;
        nextBtn.hidden = sources.length <= 1;
    };

    const open = (imageSources, index) => {
        sources = imageSources;
        showImage(index);
        lightbox.hidden = false;
        document.body.classList.add('catalogo-lightbox-open');
    };

    const close = () => {
        lightbox.hidden = true;
        document.body.classList.remove('catalogo-lightbox-open');
        resetTransform();
        img.removeAttribute('src');
    };

    const step = (delta) => {
        if (scale > 1.02) {
            resetTransform();
            return;
        }
        showImage(current + delta);
    };

    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', () => step(-1));
    nextBtn.addEventListener('click', () => step(1));

    lightbox.querySelector('.catalogo-lightbox-backdrop').addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (lightbox.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'ArrowLeft') {
            step(-1);
        } else if (event.key === 'ArrowRight') {
            step(1);
        }
    });

    stage.addEventListener('dblclick', (event) => {
        event.preventDefault();
        if (scale > 1.02) {
            resetTransform();
            return;
        }
        scale = 2.5;
        const rect = stage.getBoundingClientRect();
        translateX = (rect.width / 2 - event.clientX + rect.left) * 0.5;
        translateY = (rect.height / 2 - event.clientY + rect.top) * 0.5;
        applyTransform();
    });

    stage.addEventListener('touchstart', (event) => {
        if (event.touches.length === 2) {
            event.preventDefault();
            pinchStartDistance = getTouchDistance(event.touches);
            pinchStartScale = scale;
            isPanning = false;
            return;
        }

        if (event.touches.length === 1) {
            const now = Date.now();
            if (now - lastTap < 320) {
                event.preventDefault();
                if (scale > 1.02) {
                    resetTransform();
                } else {
                    scale = 2.5;
                    applyTransform();
                }
                lastTap = 0;
                return;
            }
            lastTap = now;

            if (scale > 1.02) {
                isPanning = true;
                panStartX = event.touches[0].clientX;
                panStartY = event.touches[0].clientY;
                panOriginX = translateX;
                panOriginY = translateY;
            }
        }
    }, { passive: false });

    stage.addEventListener('touchmove', (event) => {
        if (event.touches.length === 2) {
            event.preventDefault();
            const distance = getTouchDistance(event.touches);
            if (!pinchStartDistance) {
                pinchStartDistance = distance;
                pinchStartScale = scale;
            }
            scale = clamp(pinchStartScale * (distance / pinchStartDistance), 1, 4);
            applyTransform();
            return;
        }

        if (isPanning && event.touches.length === 1 && scale > 1.02) {
            event.preventDefault();
            translateX = panOriginX + (event.touches[0].clientX - panStartX);
            translateY = panOriginY + (event.touches[0].clientY - panStartY);
            applyTransform();
        }
    }, { passive: false });

    stage.addEventListener('touchend', (event) => {
        if (event.touches.length < 2) {
            pinchStartDistance = 0;
        }
        if (event.touches.length === 0) {
            isPanning = false;
            if (scale < 1.05) {
                resetTransform();
            }
        }
    });

    let swipeStartX = 0;
    stage.addEventListener('touchstart', (event) => {
        if (event.touches.length === 1 && scale <= 1.02) {
            swipeStartX = event.touches[0].clientX;
        }
    }, { passive: true });

    stage.addEventListener('touchend', (event) => {
        if (scale > 1.02 || event.changedTouches.length !== 1) {
            return;
        }
        const delta = event.changedTouches[0].clientX - swipeStartX;
        if (Math.abs(delta) < 50 || sources.length <= 1) {
            return;
        }
        step(delta > 0 ? -1 : 1);
    }, { passive: true });

    document.querySelectorAll('.catalogo-carousel-wrap').forEach((wrap) => {
        const buttons = [...wrap.querySelectorAll('.catalogo-carousel-zoom-btn')];
        const imageSources = buttons.map((button) => button.querySelector('img')?.src).filter(Boolean);

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.dataset.lightboxIndex || 0);
                open(imageSources, index);
            });
        });
    });
}

function createLightboxElement() {
    let lightbox = document.querySelector('.catalogo-lightbox');
    if (lightbox) {
        return lightbox;
    }

    lightbox = document.createElement('div');
    lightbox.className = 'catalogo-lightbox';
    lightbox.hidden = true;
    lightbox.innerHTML = `
        <div class="catalogo-lightbox-backdrop"></div>
        <button type="button" class="catalogo-lightbox-close" aria-label="Cerrar">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button type="button" class="catalogo-lightbox-prev" aria-label="Foto anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button type="button" class="catalogo-lightbox-next" aria-label="Foto siguiente">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <span class="catalogo-lightbox-counter"></span>
        <div class="catalogo-lightbox-stage">
            <img class="catalogo-lightbox-img" alt="Vista ampliada" draggable="false">
        </div>
        <p class="catalogo-lightbox-hint">Pellizca, doble toque o usa dos dedos para acercar</p>
    `;
    document.body.appendChild(lightbox);
    return lightbox;
}

function initLegacyGallery() {
    document.querySelectorAll('[data-gallery]').forEach((root) => {
        const main = root.querySelector('[data-gallery-main]');
        const thumbs = Array.from(root.querySelectorAll('[data-gallery-src]'));
        const prev = root.querySelector('[data-gallery-prev]');
        const next = root.querySelector('[data-gallery-next]');

        if (!main || !thumbs.length) {
            return;
        }

        let current = 0;

        const show = (index) => {
            current = (index + thumbs.length) % thumbs.length;
            const src = thumbs[current].dataset.gallerySrc || thumbs[current].getAttribute('data-gallery-src');
            main.src = src;
            thumbs.forEach((t, i) => t.classList.toggle('active', i === current));
        };

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => show(index));
        });

        prev?.addEventListener('click', () => show(current - 1));
        next?.addEventListener('click', () => show(current + 1));

        let touchStartX = 0;
        root.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        root.addEventListener('touchend', (e) => {
            const delta = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(delta) < 40) {
                return;
            }
            show(delta > 0 ? current - 1 : current + 1);
        }, { passive: true });
    });
}
