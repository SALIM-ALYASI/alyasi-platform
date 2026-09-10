document.addEventListener('DOMContentLoaded', function () {
    var legacy = document.getElementById('upgrade_verdict_text');
    if (!legacy) {
        return;
    }

    var values = window.__eventUpgradeVerdict || {};
    var group = legacy.closest('.form-group');

    legacy.id = 'upgrade_verdict_text_ar';
    legacy.name = 'upgrade_verdict_text_ar';
    legacy.rows = 6;
    legacy.maxLength = 2000;
    legacy.dir = 'rtl';
    legacy.value = values.ar || '';

    var arLabel = group ? group.querySelector('label') : null;
    if (arLabel) {
        arLabel.setAttribute('for', 'upgrade_verdict_text_ar');
        arLabel.textContent = 'تفصيل الحكم (عربي)';
    }

    var enGroup = document.createElement('div');
    enGroup.className = 'form-group form-group-full';

    var enLabel = document.createElement('label');
    enLabel.setAttribute('for', 'upgrade_verdict_text_en');
    enLabel.textContent = 'Upgrade verdict details (English)';

    var enTextarea = document.createElement('textarea');
    enTextarea.id = 'upgrade_verdict_text_en';
    enTextarea.name = 'upgrade_verdict_text_en';
    enTextarea.rows = 6;
    enTextarea.maxLength = 2000;
    enTextarea.dir = 'ltr';
    enTextarea.value = values.en || '';

    enGroup.appendChild(enLabel);
    enGroup.appendChild(enTextarea);

    if (group && group.parentNode) {
        group.parentNode.insertBefore(enGroup, group.nextSibling);
    }
});
