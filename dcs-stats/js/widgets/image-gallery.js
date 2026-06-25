(function () {
    const galleries = document.querySelectorAll('[data-gallery-carousel]');
    if (!galleries.length) return;
    const text = window.DCS_GALLERY_TEXT || {};
    const lightbox = document.createElement('div');
    lightbox.className = 'cms-gallery-lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    const close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', text.close || 'Close gallery image');
    close.textContent = '×';
    const fullImage = document.createElement('img');
    fullImage.alt = '';
    const lightboxCaption = document.createElement('p');
    lightbox.append(close, fullImage, lightboxCaption);
    document.body.appendChild(lightbox);
    let previousFocus = null;

    function hide() {
        lightbox.classList.remove('is-open');
        fullImage.removeAttribute('src');
        if (previousFocus) previousFocus.focus();
    }

    function show(button) {
        previousFocus = button;
        fullImage.src = button.dataset.fullSrc || '';
        fullImage.alt = button.querySelector('img')?.alt || '';
        lightboxCaption.textContent = button.dataset.caption || '';
        lightbox.classList.add('is-open');
        close.focus();
    }

    galleries.forEach(gallery => {
        const featured = gallery.querySelector('[data-gallery-featured]');
        const featuredImage = featured?.querySelector('img');
        const caption = gallery.querySelector('[data-gallery-caption]');
        const thumbnails = gallery.querySelectorAll('[data-gallery-thumb]');
        if (!featured || !featuredImage || !caption) return;
        featured.addEventListener('click', () => show(featured));
        thumbnails.forEach(thumbnail => thumbnail.addEventListener('click', () => {
            featured.dataset.fullSrc = thumbnail.dataset.fullSrc || '';
            featured.dataset.caption = thumbnail.dataset.caption || '';
            featuredImage.src = thumbnail.dataset.fullSrc || '';
            featuredImage.alt = thumbnail.dataset.alt || '';
            caption.textContent = thumbnail.dataset.caption || '';
            thumbnails.forEach(item => {
                const active = item === thumbnail;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-current', active ? 'true' : 'false');
            });
        }));
    });

    close.addEventListener('click', hide);
    lightbox.addEventListener('click', event => { if (event.target === lightbox) hide(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && lightbox.classList.contains('is-open')) hide(); });
}());
