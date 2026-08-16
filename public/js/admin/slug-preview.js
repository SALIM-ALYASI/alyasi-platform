(function () {
    'use strict';

    function simpleSlugify(value) {
        return (value || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function wireGroup(group) {
        var base = group.getAttribute('data-slug-base') || '';
        var slugInput = group.querySelector('[data-slug-input]');
        var sourceId = group.getAttribute('data-slug-source-id');
        var sourceInput = sourceId ? document.getElementById(sourceId) : group.querySelector('[data-slug-source]');
        var previewText = group.querySelector('[data-slug-preview-text]');
        var warning = group.querySelector('[data-slug-warning]');

        if (!slugInput || !previewText) {
            return;
        }

        function update() {
            var manual = simpleSlugify(slugInput.value);

            if (manual !== '') {
                previewText.textContent = base + manual;
                if (warning) {
                    warning.hidden = true;
                }
                return;
            }

            var fromSource = sourceInput ? simpleSlugify(sourceInput.value) : '';

            if (fromSource !== '') {
                previewText.textContent = base + fromSource;
                if (warning) {
                    warning.hidden = true;
                }
                return;
            }

            previewText.textContent = base + '(سيُنشأ تلقائيًا عند الحفظ)';

            if (warning) {
                warning.hidden = (sourceInput ? sourceInput.value.trim() : '') === '';
            }
        }

        slugInput.addEventListener('input', update);

        if (sourceInput) {
            sourceInput.addEventListener('input', update);
        }

        update();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-slug-preview]').forEach(wireGroup);
    });
})();
