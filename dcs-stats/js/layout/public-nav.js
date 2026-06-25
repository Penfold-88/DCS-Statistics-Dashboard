document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('mobileMenuToggle');
    const menuClose = document.getElementById('mobileMenuClose');
    const navBar = document.getElementById('navBar');
    const overlay = document.getElementById('mobileMenuOverlay');
    const body = document.body;

    function openMenu() {
        if (!navBar || !overlay) return;
        navBar.classList.add('mobile-menu-open');
        overlay.classList.add('active');
        body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (!navBar || !overlay) return;
        navBar.classList.remove('mobile-menu-open');
        overlay.classList.remove('active');
        body.style.overflow = '';
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', openMenu);
    }

    if (menuClose) {
        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (link.classList.contains('nav-dropdown-button')) {
                return;
            }
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });

    document.querySelectorAll('.public-nav-dropdown').forEach(dropdown => {
        const button = dropdown.querySelector('.nav-dropdown-button');
        if (!button) return;

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            document.querySelectorAll('.public-nav-dropdown.open').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('open');
                    const openButton = openDropdown.querySelector('.nav-dropdown-button');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpen = dropdown.classList.toggle('open');
            button.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.public-nav-dropdown')) {
            return;
        }

        document.querySelectorAll('.public-nav-dropdown.open').forEach(dropdown => {
            dropdown.classList.remove('open');
            const button = dropdown.querySelector('.nav-dropdown-button');
            if (button) button.setAttribute('aria-expanded', 'false');
        });
    });
});
