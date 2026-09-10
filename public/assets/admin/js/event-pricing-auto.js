(function () {
    'use strict';

    var USD_TO_OMR = 0.3845;

    function parseAmount(value) {
        var normalized = String(value || '')
            .replace(/,/g, '')
            .replace(/[^0-9.]/g, '');

        var amount = parseFloat(normalized);

        return Number.isFinite(amount) ? amount : null;
    }

    function calculateOmr(price, currency) {
        var amount = parseAmount(price);
        var code = String(currency || '').trim().toUpperCase();

        if (amount === null || amount <= 0) {
            return '';
        }

        if (code === 'USD') {
            return String(Math.ceil(amount * USD_TO_OMR));
        }

        if (code === 'OMR') {
            return String(Math.ceil(amount));
        }

        return '';
    }

    function bindPricingRow(row) {
        if (!row || row.dataset.omrAutoBound === '1') {
            return;
        }

        var priceInput = row.querySelector('[data-field="official_price"]');
        var currencyInput = row.querySelector('[data-field="official_currency"]');
        var omrInput = row.querySelector('[data-field="omr_price"]');

        if (!priceInput || !currencyInput || !omrInput) {
            return;
        }

        row.dataset.omrAutoBound = '1';

        omrInput.readOnly = true;
        omrInput.setAttribute('aria-readonly', 'true');
        omrInput.placeholder = 'يُحسب تلقائيًا';

        if (!omrInput.nextElementSibling || !omrInput.nextElementSibling.classList.contains('omr-auto-hint')) {
            var hint = document.createElement('span');
            hint.className = 'form-hint omr-auto-hint';
            hint.textContent = 'يُحسب تلقائيًا من السعر الرسمي. USD × 0.3845 مع التقريب للأعلى.';
            omrInput.insertAdjacentElement('afterend', hint);
        }

        function refresh() {
            omrInput.value = calculateOmr(priceInput.value, currencyInput.value);
        }

        priceInput.addEventListener('input', refresh);
        currencyInput.addEventListener('input', refresh);
        currencyInput.addEventListener('change', refresh);

        refresh();
    }

    function init() {
        var pricingList = document.getElementById('pricing-list');

        if (!pricingList) {
            return;
        }

        pricingList.querySelectorAll('.events-repeater-row').forEach(bindPricingRow);

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }

                    if (node.matches('.events-repeater-row')) {
                        bindPricingRow(node);
                    }

                    node.querySelectorAll('.events-repeater-row').forEach(bindPricingRow);
                });
            });
        });

        observer.observe(pricingList, {
            childList: true,
            subtree: true
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
