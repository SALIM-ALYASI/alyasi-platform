'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const articlePage = document.querySelector('.news-article-page');

    if (!articlePage) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const getFallbackImage = () => {
        return articlePage.dataset.fallbackImage
            || '/images/logo/logo-dark.png';
    };

    const getCurrentUrl = () => {
        const copyButton = articlePage.querySelector(
            '.news-article-copy-button'
        );

        return copyButton?.dataset.copyUrl
            || window.location.href;
    };

    const setButtonFeedback = (
        button,
        html,
        className = '',
        duration = 2200
    ) => {
        if (!button) {
            return;
        }

        const originalHtml = button.innerHTML;
        const originalAriaLabel = button.getAttribute('aria-label');

        if (className) {
            button.classList.add(className);
        }

        button.innerHTML = html;
        button.disabled = true;

        window.setTimeout(() => {
            button.innerHTML = originalHtml;
            button.disabled = false;

            if (className) {
                button.classList.remove(className);
            }

            if (originalAriaLabel !== null) {
                button.setAttribute(
                    'aria-label',
                    originalAriaLabel
                );
            }
        }, duration);
    };

    const copyText = async (text) => {
        if (
            navigator.clipboard
            && window.isSecureContext
        ) {
            await navigator.clipboard.writeText(text);

            return;
        }

        const textarea = document.createElement('textarea');

        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';

        document.body.appendChild(textarea);

        textarea.select();
        textarea.setSelectionRange(
            0,
            textarea.value.length
        );

        const copied = document.execCommand('copy');

        textarea.remove();

        if (!copied) {
            throw new Error('Copy command failed.');
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Copy Link
    |--------------------------------------------------------------------------
    */

    const copyButton = articlePage.querySelector(
        '.news-article-copy-button'
    );

    if (copyButton) {
        copyButton.addEventListener('click', async () => {
            const copyUrl = copyButton.dataset.copyUrl
                || getCurrentUrl();

            const copiedLabel = copyButton.dataset.copiedLabel
                || 'تم نسخ الرابط';

            const errorLabel = copyButton.dataset.copyErrorLabel
                || 'تعذر نسخ الرابط';

            try {
                await copyText(copyUrl);

                setButtonFeedback(
                    copyButton,
                    `<i class="fa-solid fa-check"></i>
                     <span>${copiedLabel}</span>`,
                    'is-copied'
                );
            } catch (error) {
                console.error(
                    'Unable to copy article link:',
                    error
                );

                setButtonFeedback(
                    copyButton,
                    `<i class="fa-solid fa-triangle-exclamation"></i>
                     <span>${errorLabel}</span>`,
                    'is-error',
                    2600
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Native Share
    |--------------------------------------------------------------------------
    |
    | يعمل فقط مع زر مخصص يحمل:
    | .news-article-native-share
    |
    | ولا يتداخل مع روابط WhatsApp وX وFacebook.
    |--------------------------------------------------------------------------
    */

    const nativeShareButton = articlePage.querySelector(
        '.news-article-native-share'
    );

    if (nativeShareButton) {
        if (!navigator.share) {
            nativeShareButton.hidden = true;
        } else {
            nativeShareButton.addEventListener(
                'click',
                async () => {
                    const shareTitle =
                        nativeShareButton.dataset.shareTitle
                        || document.title;

                    const shareText =
                        nativeShareButton.dataset.shareText
                        || shareTitle;

                    const shareUrl =
                        nativeShareButton.dataset.shareUrl
                        || getCurrentUrl();

                    try {
                        await navigator.share({
                            title: shareTitle,
                            text: shareText,
                            url: shareUrl,
                        });
                    } catch (error) {
                        if (error?.name !== 'AbortError') {
                            console.error(
                                'Unable to share article:',
                                error
                            );
                        }
                    }
                }
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Image Fallback
    |--------------------------------------------------------------------------
    */

    const fallbackImage = getFallbackImage();

    articlePage.querySelectorAll('img').forEach((image) => {
        image.addEventListener(
            'error',
            function handleImageError() {
                image.removeEventListener(
                    'error',
                    handleImageError
                );

                const currentSource = image.getAttribute('src');

                if (currentSource !== fallbackImage) {
                    image.src = fallbackImage;
                }
            }
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Reveal Animation
    |--------------------------------------------------------------------------
    */

    const animatedItems = articlePage.querySelectorAll(
        [
            '.news-article-content-card',
            '.news-article-sidebar-card',
            '.news-related-card',
            '.news-article-source',
        ].join(',')
    );

    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const revealItem = (item) => {
        item.classList.add('is-visible');
    };

    if (
        prefersReducedMotion
        || !('IntersectionObserver' in window)
    ) {
        animatedItems.forEach(revealItem);
    } else {
        animatedItems.forEach((item) => {
            item.classList.add('news-reveal-item');
        });

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    revealItem(entry.target);
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px',
            }
        );

        animatedItems.forEach((item) => {
            observer.observe(item);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Back To Top
    |--------------------------------------------------------------------------
    */

    let backToTopButton = document.querySelector(
        '.news-back-to-top'
    );

    if (!backToTopButton) {
        backToTopButton = document.createElement('button');

        backToTopButton.type = 'button';
        backToTopButton.className = 'news-back-to-top';
        backToTopButton.setAttribute(
            'aria-label',
            document.documentElement.lang === 'ar'
                ? 'العودة إلى أعلى الصفحة'
                : 'Back to top'
        );
        backToTopButton.setAttribute(
            'title',
            document.documentElement.lang === 'ar'
                ? 'العودة إلى أعلى الصفحة'
                : 'Back to top'
        );
        backToTopButton.innerHTML =
            '<i class="fa-solid fa-arrow-up"></i>';

        document.body.appendChild(backToTopButton);
    }

    const toggleBackToTop = () => {
        backToTopButton.classList.toggle(
            'is-visible',
            window.scrollY > 500
        );
    };

    let scrollTicking = false;

    window.addEventListener(
        'scroll',
        () => {
            if (scrollTicking) {
                return;
            }

            scrollTicking = true;

            window.requestAnimationFrame(() => {
                toggleBackToTop();
                scrollTicking = false;
            });
        },
        {
            passive: true,
        }
    );

    toggleBackToTop();

    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: prefersReducedMotion
                ? 'auto'
                : 'smooth',
        });
    });
});
