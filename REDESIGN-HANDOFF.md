# تقرير تسليم — إعادة تصميم ALYASI (2026-08)

آخر تحديث: 2026-08-08 — آخر commit: `b6e8ee1`

## الفكرة العامة

نبني نظام تصميم جديد بالكامل (`public/css/alyasi.css` + `public/js/alyasi.js`)
يحل محل قالب Luminary القديم (`public/luminary/`) — مو تلوين بس، إعادة بناء
حقيقية للـ HTML/classes لكل صفحة، وحدة وحدة.

الهدف النهائي: كل صفحة بالموقع تستخدم `alyasi.css`/`alyasi.js` فقط، وبعدها
نحذف مجلد `public/luminary/` نهائياً.

## ✅ خلاص (مرحلتين)

### مرحلة 1 — تلوين كل الموقع (commit `1d87abe`)
غيّرنا فقط متغيرات الألوان بجذر (`:root`) ملف Luminary القديم للباليت الجديدة
(كستنائي `#5E2324`، غامق `#1C1614`، ذهبي `#E7B65C`، كريمي `#FBF0DD`). هذا خلّى
**كل صفحة بالموقع** (حتى اللي لسا ما انبنت من جديد) تاخذ الألوان الجديدة تلقائياً،
لأن كل الـ1473 سطر بالملف مبني على متغيرات، مو ألوان مكتوبة يدوياً.

**النتيجة**: كل الصفحات متناسقة لونياً الحين، حتى قبل إعادة البناء الكاملة.

### مرحلة 2 — إعادة بناء كاملة (commit `b6e8ee1`)
- ملف تصميم جديد: `public/css/alyasi.css` (tokens، أزرار، هيدر، هيرو، sections،
  كروت، FAQ، فوتر، استجابة للشاشات).
- ملف جافاسكربت جديد: `public/js/alyasi.js` (سكرول الهيدر، القائمة الجانبية،
  reveal عند التمرير، أكورديون الأسئلة الشائعة، parallax خفيف).
- `resources/views/components/header.blade.php` — أعيد بناؤه بالكامل (نفس
  الـroutes ومنطق تبديل اللغة، classes جديدة).
- `resources/views/components/footer.blade.php` — **كان فاضي تماماً (0 بايت)**،
  بنيناه من الصفر.
- كل ملفات `resources/views/home/*.blade.php` (hero, services, portfolio,
  news, community, integrations, faq, cta) — أعيد بناؤها بالكامل.
- `resources/views/layouts/app.blade.php` — يحمّل `alyasi.css`/`alyasi.js`
  بدل Luminary، وأضفنا خط Noto Kufi Arabic لـ Google Fonts (كانت العناوين
  تحاول تستخدمه بدون ما يكون محمّل أصلاً).

## ⏳ الباقي (صفحة صفحة، بالترتيب المقترح)

كل صفحة تحتها لسا تستخدم classes من Luminary القديم (`btn-primary`,
`hero-badge`, `section-tag`, `feature-visual`...) وتعتمد على وجود
`public/luminary/templatemo-621-luminary-style.css`:

1. `resources/views/services/index.blade.php` + `resources/views/services/show.blade.php`
2. `resources/views/works/sections/*.blade.php` + `resources/views/works/show.blade.php`
3. `resources/views/news/index.blade.php` + `resources/views/news/show.blade.php` *(تأكد منها، ما ظهرت بفحص القائمة الدقيق بس تحتاج مراجعة)*
4. `resources/views/community/show.blade.php` + `resources/views/community/sections/*.blade.php` *(راجع index.blade.php كمان)*
5. `resources/views/about/index.blade.php`
6. `resources/views/contact/index.blade.php`
7. `resources/views/social-links/index.blade.php` + `resources/views/social-links/sections/*.blade.php`
8. `resources/views/legal/privacy.blade.php` + `resources/views/legal/terms.blade.php`
9. `resources/views/components/error-page.blade.php` (صفحات 403/404/419/500/503)
10. `resources/views/partials/reviews.blade.php` (مستخدم جوا works/show + services/show)

**صفحات جانبية بأولوية أقل** (routes منفصلة، مو جزء من تنقل الموقع الرئيسي):
`car-wash.blade.php`, `markify.blade.php`, `ra3i-swait.blade.php` — اسأل قبل
تعيد بناءها، ممكن تكون صفحات شخصية/مشاريع جانبية مو جزء من العلامة.

⚠️ **مهم**: القائمة أعلاه من بحث نصي (grep) عن أسماء classes قديمة معروفة —
مو مضمونة 100%. قبل ما تعتبر أي صفحة "خلصت"، جرّبها بالمتصفح وتأكد الشكل صحيح.

## 🎨 دليل الـ classes (القديم → الجديد)

| القديم (Luminary) | الجديد (alyasi.css) |
|---|---|
| `.btn-primary` / `.btn-secondary` | `.btn.btn--primary` / `.btn.btn--secondary` / `.btn.btn--light` |
| `.hero` + `.hero-small` | `.hero` + `.hero--compact` |
| `.hero-badge` / `.hero-badge-dot` | `.pill` / `.pill__dot` |
| `.hero-sub` | `.hero__desc` |
| `.hero-ctas` | `.hero__ctas` |
| `.hero-metrics` / `.metric-value` / `.metric-label` | `.hero__metrics` / `.metric__value` / `.metric__label` |
| `.section-heading` / `.section-tag` | `.section-head` / `.eyebrow` |
| `.section-title strong` | `.section-title em` |
| `.feature` / `.feature-visual` / `.feature-content` | `.feature` / `.feature__visual` / `.feature__content` |
| `.faq-item` (native `<details>`) | `.faq-item` (زر `<button class="faq-item__question">` + `<div class="faq-item__answer">` — يحتاج JS، موجود بـ`alyasi.js` تلقائياً لأي `.faq-list` بأي صفحة) |
| `.cta-card` | `.cta-band` (قسم كامل العرض) أو `.cta-band.cta-band--card` (بطاقة بحدود مثل community) |

## 🧭 خطوات ترحيل أي صفحة (نفس الطريقة كل مرة)

1. اقرأ ملف الـ Blade الحالي كامل، افهم البيانات الديناميكية (models, routes).
2. أعد كتابة الـ HTML بالـ classes الجديدة من الجدول فوق.
3. لو احتجت مكوّن جديد مو موجود بـ`alyasi.css` (مثلاً معرض صور، فورم تواصل)،
   ضيفه بآخر `alyasi.css` بنفس أسلوب التسمية (`.block__part`)، واستخدم متغيرات
   الألوان الموجودة (`var(--gold)`, `var(--maroon)`, `var(--cream)`...) —
   لا تكتب ألوان hex مباشرة.
4. جرّب الصفحة بالمتصفح (ديسكتوب + موبايل)، تأكد ما فيه أخطاء console.
5. اعمل commit منفصل لكل صفحة (سهل التراجع لو صار خطأ).

## ⚠️ خطأ وقعنا فيه، لا تكرره

بملف `alyasi.css` فيه قاعدة `.hero > *:not(.hero__bg):not(.hero__scrim):not(.hero__grid) { position: relative; }`
— لازم تفضل بهذا الشكل بالضبط. لو حذفت الـ`:not()`، خلفية الهيرو (`.hero__bg`)
بتفقد `position:absolute` وتختفي الصورة. نفس الفخ ممكن يصير بأي عنصر جديد
تضيفه كطبقة خلفية داخل `.hero`.

## 📂 الملفات المهمة

- `public/css/alyasi.css` — كل التصميم الجديد (ملف واحد، ينمو صفحة بصفحة)
- `public/js/alyasi.js` — كل التفاعل الجديد
- `public/luminary/` — القديم، **لا تحذفه** إلا بعد ما كل صفحة فوق تتأكد منها
- `public/luminary/templatemo-621-luminary-style.css.bak-*` — نسخة احتياطية من قبل تعديل الألوان (محلي فقط، مو على git)

## 🖼️ الصور

الصور الخمسة المعتمدة موجودة بـ `public/images/home/` (WebP، محسّنة الحجم):
`hero-bg.webp`, `service-featured.webp`, `work-delivery-app.webp`,
`work-technova-brand.webp`, `work-managex-saas.webp`.

3 مشاريع "أعمالي" بالصفحة الرئيسية (تطبيق توصيل، هوية بصرية، SaaS) هي **نماذج
توضيحية مو عملاء حقيقيين** — مكتوب هذا صراحة بحقل `short_description` بقاعدة
البيانات (`database/seeders/WorkDemoSeeder.php`)، لازم تنحذف أو تنستبدل
بمشاريع حقيقية أول ما تتوفر.
