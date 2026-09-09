# مكتبات وأدوات مشروع alyasi-platform

آخر تحديث: 2026-08-23

## البيئة
- PHP 8.4.24 (الحد الأدنى المطلوب: ^8.3)
- Node.js v22.23.1 / npm 10.9.8
- Laravel Framework ^13.8

## مكتبات PHP (Composer) — Production
| المكتبة | الإصدار | الاستخدام |
|---|---|---|
| laravel/framework | ^13.8 | الفريمورك الأساسي |
| laravel/tinker | ^3.0 | REPL/console تفاعلي |
| mews/purifier | ^3.4 | تنقية/تعقيم HTML (HTML Purifier) |
| spatie/laravel-sitemap | ^8.2 | توليد خرائط الموقع (sitemap) |

## مكتبات PHP (Composer) — Development فقط
| المكتبة | الإصدار | الاستخدام |
|---|---|---|
| barryvdh/laravel-debugbar | ^4.4 | شريط تصحيح الأخطاء |
| laravel/telescope | ^5.21 | مراقبة الطلبات والاستعلامات (debugging) |
| laravel/pail | ^1.2.5 | متابعة اللوقات الحية |
| laravel/pao | ^1.0.6 | أداة مساعدة Laravel |
| laravel/pint | ^1.27 | تنسيق الكود (code style) |
| fakerphp/faker | ^1.23 | بيانات وهمية للاختبار |
| mockery/mockery | ^1.6 | Mocking للاختبارات |
| phpunit/phpunit | ^12.5.12 | تشغيل الاختبارات |
| nunomaduro/collision | ^8.6 | عرض الأخطاء بشكل واضح في الكونسول |

## مكتبات JavaScript (npm) — Development
| المكتبة | الإصدار | الاستخدام |
|---|---|---|
| vite | ^8.0.0 | أداة البناء (bundler) |
| laravel-vite-plugin | ^3.1 | ربط Vite بـ Laravel |
| tailwindcss | ^4.0.0 | إطار عمل CSS |
| @tailwindcss/vite | ^4.0.0 | تكامل Tailwind مع Vite |
| concurrently | ^9.0.1 | تشغيل عدة أوامر بالتوازي (dev script) |

⚠️ ملاحظة: `package.json` في جذر المشروع فاضي (0 بايت) — البيانات أعلاه مسحوبة من `package-lock.json`. يفضّل إعادة توليد `package.json` قبل أي `npm install` جديد.

## إعدادات المشروع الافتراضية (.env.example)
- قاعدة البيانات: SQLite
- Session driver: database
- Queue: database
- Cache: database
- Mail: log (لا يرسل فعلياً في البيئة الافتراضية)
- Filesystem: local
