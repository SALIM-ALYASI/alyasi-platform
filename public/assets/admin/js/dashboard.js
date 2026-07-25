'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    const sidebar = document.getElementById('admin-sidebar');
    const sidebarOpenButton = document.getElementById('sidebar-open');
    const sidebarCloseButton = document.getElementById('sidebar-close');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    const adminMenuToggle = document.getElementById('admin-menu-toggle');
    const adminDropdown = document.getElementById(
        'admin-account-dropdown'
    );

    const fullscreenToggle = document.getElementById(
        'fullscreen-toggle'
    );

    /**
     * Sidebar controls.
     */
    const openSidebar = () => {
        if (! sidebar) {
            return;
        }

        sidebar.classList.add('is-open');
        sidebarOverlay?.classList.add('is-visible');
        body.classList.add('sidebar-is-open');

        sidebarOpenButton?.setAttribute(
            'aria-expanded',
            'true'
        );
    };

    const closeSidebar = () => {
        if (! sidebar) {
            return;
        }

        sidebar.classList.remove('is-open');
        sidebarOverlay?.classList.remove('is-visible');
        body.classList.remove('sidebar-is-open');

        sidebarOpenButton?.setAttribute(
            'aria-expanded',
            'false'
        );
    };

    sidebarOpenButton?.addEventListener(
        'click',
        openSidebar
    );

    sidebarCloseButton?.addEventListener(
        'click',
        closeSidebar
    );

    sidebarOverlay?.addEventListener(
        'click',
        closeSidebar
    );

    /**
     * Close sidebar when a navigation link is clicked on mobile.
     */
    sidebar?.querySelectorAll('.sidebar-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 980) {
                closeSidebar();
            }
        });
    });

    /**
     * Admin account dropdown.
     */
    const closeAdminDropdown = () => {
        if (! adminDropdown || ! adminMenuToggle) {
            return;
        }

        adminDropdown.classList.remove('is-open');

        adminMenuToggle.setAttribute(
            'aria-expanded',
            'false'
        );
    };

    const toggleAdminDropdown = () => {
        if (! adminDropdown || ! adminMenuToggle) {
            return;
        }

        const dropdownIsOpen = adminDropdown.classList.toggle(
            'is-open'
        );

        adminMenuToggle.setAttribute(
            'aria-expanded',
            dropdownIsOpen ? 'true' : 'false'
        );
    };

    adminMenuToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleAdminDropdown();
    });

    adminDropdown?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', () => {
        closeAdminDropdown();
    });

    /**
     * Fullscreen controls.
     */
    const updateFullscreenIcon = () => {
        if (! fullscreenToggle) {
            return;
        }

        const icon = fullscreenToggle.querySelector('i');
        const fullscreenIsActive = Boolean(
            document.fullscreenElement
        );

        fullscreenToggle.setAttribute(
            'aria-label',
            fullscreenIsActive
                ? 'الخروج من ملء الشاشة'
                : 'ملء الشاشة'
        );

        fullscreenToggle.setAttribute(
            'title',
            fullscreenIsActive
                ? 'الخروج من ملء الشاشة'
                : 'ملء الشاشة'
        );

        if (! icon) {
            return;
        }

        icon.classList.toggle(
            'fa-expand',
            ! fullscreenIsActive
        );

        icon.classList.toggle(
            'fa-compress',
            fullscreenIsActive
        );
    };

    const toggleFullscreen = async () => {
        try {
            if (! document.fullscreenElement) {
                await document.documentElement.requestFullscreen();
            } else {
                await document.exitFullscreen();
            }
        } catch (error) {
            console.error(
                'تعذر تغيير وضع ملء الشاشة:',
                error
            );
        }
    };

    fullscreenToggle?.addEventListener(
        'click',
        toggleFullscreen
    );

    document.addEventListener(
        'fullscreenchange',
        updateFullscreenIcon
    );

    /**
     * Keyboard accessibility.
     */
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeSidebar();
        closeAdminDropdown();
    });

    /**
     * Reset sidebar state when changing screen size.
     */
    window.addEventListener('resize', () => {
        if (window.innerWidth > 980) {
            closeSidebar();
        }
    });
});