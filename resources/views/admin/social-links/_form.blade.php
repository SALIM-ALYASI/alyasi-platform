@php
    $isEdit = isset($socialLink);
@endphp

<div class="form-grid">

    <div class="form-group form-group-full">
        <label for="url">رابط الحساب</label>

        <input
            type="url"
            id="url"
            name="url"
            value="{{ old('url', $socialLink->url ?? '') }}"
            placeholder="مثال: https://www.instagram.com/alyasi_mbrmj/"
            autocomplete="url"
            required
        >

        <small class="form-hint">
            الصق رابط الحساب وسيتم تعبئة باقي البيانات تلقائيًا.
        </small>

        <div
            id="social_detect_status"
            class="social-detect-status"
            aria-live="polite"
        ></div>

        @error('url')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="name">اسم المنصة</label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $socialLink->name ?? '') }}"
            placeholder="مثال: Instagram"
            required
        >

        @error('name')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="platform">معرّف المنصة</label>

        <input
            type="text"
            id="platform"
            name="platform"
            value="{{ old('platform', $socialLink->platform ?? '') }}"
            placeholder="مثال: instagram"
            required
        >

        @error('platform')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="username">اسم المستخدم</label>

        <input
            type="text"
            id="username"
            name="username"
            value="{{ old('username', $socialLink->username ?? '') }}"
            placeholder="@username"
        >

        @error('username')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="icon">كلاس الأيقونة</label>

        <input
            type="text"
            id="icon"
            name="icon"
            value="{{ old('icon', $socialLink->icon ?? 'fa-solid fa-link') }}"
            placeholder="fa-brands fa-instagram"
            required
        >

        @error('icon')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="color">اللون</label>

        <div class="color-field">
            <input
                type="color"
                id="color_picker"
                value="{{ old('color', $socialLink->color ?? '#5E2324') }}"
                aria-label="اختيار اللون"
            >

            <input
                type="text"
                id="color"
                name="color"
                value="{{ old('color', $socialLink->color ?? '#5E2324') }}"
                placeholder="#5E2324"
            >
        </div>

        @error('color')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="sort_order">ترتيب الظهور</label>

        <input
            type="number"
            id="sort_order"
            name="sort_order"
            value="{{ old('sort_order', $socialLink->sort_order ?? 0) }}"
            min="0"
            required
        >

        @error('sort_order')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group form-group-full">
        <div class="form-checks">

            <label class="check-option">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $socialLink->is_active ?? true))
                >

                <span>نشط ويظهر في الموقع</span>
            </label>

            <label class="check-option">
                <input
                    type="checkbox"
                    name="open_new_tab"
                    value="1"
                    @checked(old('open_new_tab', $socialLink->open_new_tab ?? true))
                >

                <span>فتح الرابط في تبويب جديد</span>
            </label>

        </div>
    </div>

</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'حفظ التعديلات' : 'إضافة المنصة' }}
    </button>

    <a
        href="{{ route('admin.social-links.index') }}"
        class="btn btn-secondary"
    >
        إلغاء
    </a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fields = {
        url: document.getElementById('url'),
        name: document.getElementById('name'),
        platform: document.getElementById('platform'),
        username: document.getElementById('username'),
        icon: document.getElementById('icon'),
        color: document.getElementById('color'),
        colorPicker: document.getElementById('color_picker'),
        sortOrder: document.getElementById('sort_order'),
        status: document.getElementById('social_detect_status'),
    };

    const platforms = [
        {
            key: 'instagram',
            hosts: ['instagram.com', 'www.instagram.com'],
            name: 'Instagram',
            icon: 'fa-brands fa-instagram',
            color: '#E4405F',
            order: 1,
        },
        {
            key: 'x',
            hosts: ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'],
            name: 'X',
            icon: 'fa-brands fa-x-twitter',
            color: '#000000',
            order: 2,
        },
        {
            key: 'facebook',
            hosts: ['facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com'],
            name: 'Facebook',
            icon: 'fa-brands fa-facebook-f',
            color: '#1877F2',
            order: 3,
        },
        {
            key: 'linkedin',
            hosts: ['linkedin.com', 'www.linkedin.com'],
            name: 'LinkedIn',
            icon: 'fa-brands fa-linkedin-in',
            color: '#0A66C2',
            order: 4,
        },
        {
            key: 'youtube',
            hosts: ['youtube.com', 'www.youtube.com', 'youtu.be'],
            name: 'YouTube',
            icon: 'fa-brands fa-youtube',
            color: '#FF0000',
            order: 5,
        },
        {
            key: 'tiktok',
            hosts: ['tiktok.com', 'www.tiktok.com'],
            name: 'TikTok',
            icon: 'fa-brands fa-tiktok',
            color: '#000000',
            order: 6,
        },
        {
            key: 'snapchat',
            hosts: ['snapchat.com', 'www.snapchat.com'],
            name: 'Snapchat',
            icon: 'fa-brands fa-snapchat',
            color: '#FFFC00',
            order: 7,
        },
        {
            key: 'whatsapp',
            hosts: [
                'wa.me',
                'api.whatsapp.com',
                'whatsapp.com',
                'www.whatsapp.com',
            ],
            name: 'WhatsApp',
            icon: 'fa-brands fa-whatsapp',
            color: '#25D366',
            order: 8,
        },
        {
            key: 'telegram',
            hosts: ['t.me', 'telegram.me', 'telegram.org'],
            name: 'Telegram',
            icon: 'fa-brands fa-telegram',
            color: '#26A5E4',
            order: 9,
        },
        {
            key: 'github',
            hosts: ['github.com', 'www.github.com'],
            name: 'GitHub',
            icon: 'fa-brands fa-github',
            color: '#181717',
            order: 10,
        },
        {
            key: 'threads',
            hosts: ['threads.net', 'www.threads.net'],
            name: 'Threads',
            icon: 'fa-brands fa-threads',
            color: '#000000',
            order: 11,
        },
        {
            key: 'discord',
            hosts: ['discord.com', 'www.discord.com', 'discord.gg'],
            name: 'Discord',
            icon: 'fa-brands fa-discord',
            color: '#5865F2',
            order: 12,
        },
        {
            key: 'pinterest',
            hosts: ['pinterest.com', 'www.pinterest.com', 'pin.it'],
            name: 'Pinterest',
            icon: 'fa-brands fa-pinterest-p',
            color: '#E60023',
            order: 13,
        },
        {
            key: 'behance',
            hosts: ['behance.net', 'www.behance.net'],
            name: 'Behance',
            icon: 'fa-brands fa-behance',
            color: '#1769FF',
            order: 14,
        },
    ];

    function normalizeUrl(value) {
        let url = value.trim();

        if (!url) {
            return '';
        }

        if (!/^https?:\/\//i.test(url)) {
            url = `https://${url}`;
        }

        return url;
    }

    function setStatus(message = '', type = '') {
        if (!fields.status) {
            return;
        }

        fields.status.textContent = message;
        fields.status.className = `social-detect-status ${type}`.trim();
    }

    function extractUsername(parsedUrl, platform) {
        const pathParts = parsedUrl.pathname
            .split('/')
            .map(part => decodeURIComponent(part.trim()))
            .filter(Boolean);

        if (platform.key === 'whatsapp') {
            if (parsedUrl.hostname === 'api.whatsapp.com') {
                return parsedUrl.searchParams
                    .get('phone')
                    ?.replace(/\D/g, '') ?? '';
            }

            return (pathParts[0] ?? '').replace(/\D/g, '');
        }

        if (platform.key === 'linkedin') {
            if (
                ['in', 'company', 'school'].includes(
                    (pathParts[0] ?? '').toLowerCase()
                )
            ) {
                return pathParts[1]
                    ? `@${pathParts[1].replace(/^@/, '')}`
                    : '';
            }
        }

        if (platform.key === 'youtube') {
            const handle = pathParts.find(part => part.startsWith('@'));

            if (handle) {
                return handle;
            }

            if (
                ['channel', 'c', 'user'].includes(
                    (pathParts[0] ?? '').toLowerCase()
                ) &&
                pathParts[1]
            ) {
                return pathParts[1];
            }
        }

        const ignoredParts = [
            'home',
            'share',
            'invite',
            'watch',
            'reel',
            'reels',
            'p',
            'posts',
            'explore',
            'company',
            'in',
            'channel',
            'user',
        ];

        const username = pathParts.find(part => {
            return !ignoredParts.includes(part.toLowerCase());
        });

        if (!username) {
            return '';
        }

        return username.startsWith('@')
            ? username
            : `@${username}`;
    }

    function detectPlatform() {
        if (!fields.url) {
            return;
        }

        const normalizedUrl = normalizeUrl(fields.url.value);

        if (!normalizedUrl) {
            setStatus();
            return;
        }

        try {
            const parsedUrl = new URL(normalizedUrl);
            const hostname = parsedUrl.hostname.toLowerCase();

            const platform = platforms.find(item => {
                return item.hosts.includes(hostname);
            });

            if (!platform) {
                setStatus(
                    'لم يتم التعرف على المنصة. يمكنك إدخال البيانات يدويًا.',
                    'is-warning'
                );

                return;
            }

            fields.url.value = normalizedUrl;

            if (fields.name) {
                fields.name.value = platform.name;
            }

            if (fields.platform) {
                fields.platform.value = platform.key;
            }

            if (fields.username) {
                fields.username.value = extractUsername(parsedUrl, platform);
            }

            if (fields.icon) {
                fields.icon.value = platform.icon;
            }

            if (fields.color) {
                fields.color.value = platform.color;
            }

            if (fields.colorPicker) {
                fields.colorPicker.value = platform.color;
            }

            if (
                fields.sortOrder &&
                (
                    fields.sortOrder.value === '' ||
                    Number(fields.sortOrder.value) === 0
                )
            ) {
                fields.sortOrder.value = platform.order;
            }

            setStatus(
                `تم التعرف على منصة ${platform.name} وتعبئة البيانات تلقائيًا.`,
                'is-success'
            );
        } catch (error) {
            setStatus(
                'الرابط غير صحيح. تأكد من كتابة رابط الحساب كاملًا.',
                'is-error'
            );
        }
    }

    let detectionTimer = null;

    fields.url?.addEventListener('input', function () {
        window.clearTimeout(detectionTimer);

        detectionTimer = window.setTimeout(
            detectPlatform,
            350
        );
    });

    fields.url?.addEventListener('paste', function () {
        window.setTimeout(detectPlatform, 50);
    });

    fields.url?.addEventListener('blur', detectPlatform);

    fields.colorPicker?.addEventListener('input', function () {
        if (fields.color) {
            fields.color.value = fields.colorPicker.value;
        }
    });

    fields.color?.addEventListener('input', function () {
        const value = fields.color.value.trim();

        if (
            fields.colorPicker &&
            /^#[0-9A-Fa-f]{6}$/.test(value)
        ) {
            fields.colorPicker.value = value;
        }
    });

    if (fields.url?.value) {
        detectPlatform();
    }
});
</script>
@endpush