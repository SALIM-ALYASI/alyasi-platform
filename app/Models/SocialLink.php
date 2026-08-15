<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SocialLink extends Model
{
    /**
     * الحسابات الرسمية الأساسية اللي لازم تظهر دائمًا في كل مكان
     * (الفوتر، صفحة روابط التواصل، صفحة تواصل معنا)، حتى لو غابت
     * أو تعطّلت بياناتها في قاعدة البيانات.
     */
    private const CORE_DEFAULTS = [
        'whatsapp' => [
            'name' => 'واتساب',
            'platform' => 'whatsapp',
            'url' => 'https://wa.me/message/WJELG5R5UVIBO1',
            'icon' => 'fa-brands fa-whatsapp',
            'open_new_tab' => true,
        ],
        'x' => [
            'name' => 'X (تويتر)',
            'platform' => 'x',
            'url' => 'https://x.com/alyasi_mbrmj',
            'icon' => 'fa-brands fa-x-twitter',
            'open_new_tab' => true,
        ],
        'instagram' => [
            'name' => 'إنستغرام',
            'platform' => 'instagram',
            'url' => 'https://www.instagram.com/alyasi_mbrmj',
            'icon' => 'fa-brands fa-instagram',
            'open_new_tab' => true,
        ],
    ];

    protected $fillable = [
        'name',
        'platform',
        'url',
        'username',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'open_new_tab',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'open_new_tab' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * روابط التواصل المفعّلة للعرض في الموقع: الحسابات الثلاثة
     * الأساسية (واتساب، X، إنستغرام) مضمونة الظهور دائمًا - ببياناتها
     * من قاعدة البيانات لو موجودة ومفعّلة، أو بالرابط الرسمي الثابت
     * كاحتياط - تليها بقية الحسابات المفعّلة حسب ترتيبها.
     */
    public static function forDisplay(): Collection
    {
        $active = static::query()->active()->ordered()->get();

        $byPlatform = $active->keyBy(fn (self $link) => strtolower($link->platform));

        $core = collect(self::CORE_DEFAULTS)->map(function (array $defaults, string $platform) use ($byPlatform) {
            $dbLink = $byPlatform->get($platform);

            return $dbLink && filled($dbLink->url) ? $dbLink : new self($defaults);
        })->values();

        $rest = $active->reject(
            fn (self $link) => array_key_exists(strtolower($link->platform), self::CORE_DEFAULTS)
        )->values();

        return $core->concat($rest);
    }
}
