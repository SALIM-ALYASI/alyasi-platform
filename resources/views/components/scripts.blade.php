<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.getElementById('navToggle');
        const mobileMenu = document.getElementById('mobileMenu');

        if (menuButton && mobileMenu) {
            const menuLinks = mobileMenu.querySelectorAll('.mobile-menu-link');
            let menuOpen = false;

            const openMenu = function () {
                menuOpen = true;
                menuButton.classList.add('active');
                menuButton.setAttribute('aria-expanded', 'true');
                mobileMenu.classList.add('open');
                mobileMenu.setAttribute('aria-hidden', 'false');
                document.body.classList.add('menu-open');
            };

            const closeMenu = function () {
                if (!menuOpen) return;
                menuOpen = false;
                menuButton.classList.remove('active');
                menuButton.setAttribute('aria-expanded', 'false');
                mobileMenu.classList.remove('open');
                mobileMenu.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('menu-open');
            };

            menuButton.addEventListener('click', function () {
                menuOpen ? closeMenu() : openMenu();
            });

            menuLinks.forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 1024) closeMenu();
            });
        }

        const revealElements = document.querySelectorAll('.reveal');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    threshold: 0.12,
                }
            );

            revealElements.forEach(function (element) {
                observer.observe(element);
            });
        } else {
            revealElements.forEach(function (element) {
                element.classList.add('visible');
            });
        }
    });
</script>

@stack('scripts') 