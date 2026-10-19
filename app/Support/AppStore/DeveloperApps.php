<?php

namespace App\Support\AppStore;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * تطبيقات سالم المنشورة في App Store (حساب المطوّر «Salim Alhajri»)، من واجهة
 * Apple العامة (iTunes Lookup). البيانات بملف محلي، وأيقونات التطبيقات محفوظة
 * محليًا، وتتحدّث تلقائيًا كل أسبوع بعد الاستجابة (بدون جدولة سيرفر).
 */
final class DeveloperApps
{
    /**
     * موقوف (19 أكتوبر 2026، طلب سالم): ارتباط حساب المطوّر هذا بسالم غير مؤكد
     * (اشتراكه في Apple Developer متوقف، والحساب فيه تحديثات حديثة). القسم ما يظهر
     * والمزامنة ما تشتغل لين يتأكد سالم من التطبيقات.
     */
    public const ENABLED = false;

    public const DEVELOPER_ID = 1191312496;

    public const DEVELOPER_URL = 'https://apps.apple.com/om/developer/salim-alhajri/id1191312496';

    /** التطبيقات البارزة بصفحة الأعمال وصفحة المؤسس (بالترتيب) -- أرقام App Store. */
    public const FEATURED = [
        1597995054, // Bidiyah
        1635810837, // واصل فلاش
        1253076174, // Salalah
        6468473477, // مركز العين للأبحاث
        1226320302, // روايات وكتب
        1465074581, // Camel Racing
        6747723818, // بلان
        6758126407, // ScannerDoc
    ];

    private const FILE = 'app/app-store-apps.json';

    /** أسماء فئات App Store بالعربي (الصفحة العربية). */
    private const GENRES_AR = [
        'Travel' => 'سفر وسياحة', 'Shopping' => 'تسوق', 'Book' => 'كتب', 'Books' => 'كتب', 'Games' => 'ألعاب',
        'Utilities' => 'أدوات', 'Productivity' => 'إنتاجية', 'Business' => 'أعمال', 'Food & Drink' => 'طعام وشراب',
        'Photo & Video' => 'صور وفيديو', 'Entertainment' => 'ترفيه', 'Education' => 'تعليم', 'Lifestyle' => 'نمط الحياة',
    ];

    public static function genre(?string $genre): ?string
    {
        return app()->getLocale() === 'ar' ? (self::GENRES_AR[$genre] ?? $genre) : $genre;
    }

    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        $path = storage_path(self::FILE);
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return $data['apps'] ?? [];
    }

    public static function syncedAt(): ?string
    {
        $path = storage_path(self::FILE);

        return is_file($path) ? (json_decode((string) file_get_contents($path), true)['synced_at'] ?? null) : null;
    }

    /** @return list<array<string, mixed>> */
    public static function featured(): array
    {
        if (! self::ENABLED) {
            return [];
        }

        $byId = collect(self::all())->keyBy('id');

        return collect(self::FEATURED)->map(fn ($id) => $byId[$id] ?? null)->filter()->values()->all();
    }

    /** تحديث أسبوعي بعد الاستجابة (لو الملف قديم أو مب موجود). */
    public static function refreshLaterIfStale(): void
    {
        if (! self::ENABLED) {
            return;
        }

        $path = storage_path(self::FILE);

        if (is_file($path) && filemtime($path) > now()->subDays(7)->getTimestamp()) {
            return;
        }

        app()->terminating(function (): void {
            try {
                self::sync();
            } catch (\Throwable $e) {
                Log::warning('app_store_sync_failed', ['error' => $e->getMessage()]);
            }
        });
    }

    public static function sync(): int
    {
        $response = Http::timeout(20)->get('https://itunes.apple.com/lookup', [
            'id' => self::DEVELOPER_ID, 'entity' => 'software', 'country' => 'om', 'limit' => 200,
        ]);

        $results = collect($response->json('results') ?? [])->where('wrapperType', 'software')->values();

        if ($results->isEmpty()) {
            return 0; // ما نمسح القائمة المحفوظة لو فشل الطلب
        }

        $iconDir = public_path('images/apps');
        if (! is_dir($iconDir)) {
            mkdir($iconDir, 0755, true);
        }

        $apps = $results->map(function (array $app) use ($iconDir) {
            $id = (int) $app['trackId'];
            $icon = "images/apps/{$id}.jpg";
            $artwork = $app['artworkUrl512'] ?? $app['artworkUrl100'] ?? null;

            if ($artwork && ! is_file(public_path($icon))) {
                $image = Http::timeout(15)->get(str_replace('512x512bb', '256x256bb', $artwork));
                if ($image->successful()) {
                    file_put_contents(public_path($icon), $image->body());
                }
            }

            return [
                'id' => $id,
                'name' => $app['trackName'],
                'url' => strtok((string) $app['trackViewUrl'], '?'),
                'icon' => is_file(public_path($icon)) ? $icon : null,
                'genre' => $app['primaryGenreName'] ?? null,
                'released' => substr((string) ($app['releaseDate'] ?? ''), 0, 10) ?: null,
                'updated' => substr((string) ($app['currentVersionReleaseDate'] ?? ''), 0, 10) ?: null,
                'version' => $app['version'] ?? null,
                'rating' => isset($app['averageUserRating']) ? round((float) $app['averageUserRating'], 1) : null,
                'ratings_count' => (int) ($app['userRatingCount'] ?? 0),
            ];
        })->sortByDesc('released')->values()->all();

        file_put_contents(storage_path(self::FILE), json_encode([
            'developer_id' => self::DEVELOPER_ID,
            'synced_at' => now()->toIso8601String(),
            'apps' => $apps,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return count($apps);
    }
}
