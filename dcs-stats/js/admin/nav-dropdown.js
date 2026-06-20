document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');

    dropdownToggles.forEach(function(toggle, index) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const dropdown = this.parentNode;
            const menu = dropdown.querySelector('.nav-dropdown-menu');
            const arrow = this.querySelector('.dropdown-arrow');

            document.querySelectorAll('.nav-dropdown').forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('open');
                    const otherMenu = otherDropdown.querySelector('.nav-dropdown-menu');
                    const otherArrow = otherDropdown.querySelector('.dropdown-arrow');
                    if (otherMenu) {
                        otherMenu.classList.remove('open');
                    }
                    if (otherArrow) {
                        otherArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });

            const isCurrentlyOpen = dropdown.classList.contains('open');

            if (isCurrentlyOpen) {
                dropdown.classList.remove('open');
                if (menu) {
                    menu.classList.remove('open');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            } else {
                dropdown.classList.add('open');
                if (menu) {
                    menu.classList.add('open');
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            const currentPath = window.location.pathname;
            const optionPages = ['settings.php', 'metadata.php', 'language_settings.php', 'custom_links.php', 'settings_backup.php', 'api_settings.php', 'api_health.php', 'themes.php', 'discord_settings.php', 'squadron_settings.php', 'cms_settings.php', 'cms_pages.php', 'admins.php', 'permissions.php', 'maintenance.php', 'update.php'];
            const isOnOptionPage = optionPages.some(page => currentPath.includes(page));

            if (!isOnOptionPage) {
                document.querySelectorAll('.nav-dropdown.open').forEach(function(dropdown) {
                    dropdown.classList.remove('open');
                    const menu = dropdown.querySelector('.nav-dropdown-menu');
                    const arrow = dropdown.querySelector('.dropdown-arrow');
                    if (menu) {
                        menu.classList.remove('open');
                    }
                    if (arrow) {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                });
            }
        }
    });
});
