<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.querySelector('[data-menu-toggle]');
        const mobileMenu = document.querySelector('[data-mobile-menu]');

        if (menuButton && mobileMenu) {
            menuButton.addEventListener('click', function () {
                mobileMenu.classList.toggle('active');
                menuButton.classList.toggle('active');
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