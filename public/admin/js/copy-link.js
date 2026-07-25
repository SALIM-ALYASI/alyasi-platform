document.addEventListener('DOMContentLoaded', () => {

    const buttons = document.querySelectorAll('.js-copy-service-link');

    if (!buttons.length) {
        return;
    }

    const message = document.getElementById('copyMessage');

    let timer = null;

    function showMessage() {

        if (!message) {
            return;
        }

        message.classList.add('is-visible');

        clearTimeout(timer);

        timer = setTimeout(() => {
            message.classList.remove('is-visible');
        }, 2200);
    }

    async function copy(text) {

        if (navigator.clipboard && window.isSecureContext) {

            await navigator.clipboard.writeText(
                decodeURIComponent(text)
            );

            return;
        }

        const textarea = document.createElement('textarea');

        textarea.value = decodeURIComponent(text);

        textarea.style.position = 'fixed';

        textarea.style.opacity = '0';

        document.body.appendChild(textarea);

        textarea.select();

        document.execCommand('copy');

        textarea.remove();
    }

    buttons.forEach(button => {

        button.addEventListener('click', async () => {

            const url = button.dataset.copyUrl;

            if (!url) {
                return;
            }

            try {

                await copy(url);

                showMessage();

            } catch (e) {

                alert('تعذر نسخ الرابط');

            }

        });

    });

});