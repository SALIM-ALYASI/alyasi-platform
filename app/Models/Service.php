<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    /**
     * الحقول المسموح بحفظها جماعيًا.
     */
    protected $fillable = [
        'title_ar',
        'description_ar',
        'title_en',
        'description_en',
        'image',
        'is_active',
        'price_from',
        'price_is_starting',
        'price_unit_ar',
        'price_unit_en',
        'price_note_ar',
        'price_note_en',
    ];

    /**
     * تحويل أنواع الحقول تلقائيًا.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_from' => 'decimal:3',
            'price_is_starting' => 'boolean',
        ];
    }

    /**
     * جلب الخدمات النشطة فقط.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * ترتيب الخدمات من الأحدث إلى الأقدم.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('id');
    }

    /**
     * البحث في بيانات الخدمة العربية والإنجليزية.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search): void {
            $subQuery
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")
                ->orWhere('description_ar', 'like', "%{$search}%")
                ->orWhere('description_en', 'like', "%{$search}%");
        });
    }

    /**
     * جميع الروابط الدائمة التابعة للخدمة.
     */
    public function permalinks(): MorphMany
    {
        return $this->morphMany(
            Permalink::class,
            'linkable'
        );
    }

    /**
     * جلب الرابط الدائم حسب اللغة.
     */
    public function permalink(
        ?string $locale = null
    ): ?Permalink {
        $locale ??= app()->getLocale();

        return $this->permalinks()
            ->where('locale', $locale)
            ->first();
    }

    /**
     * جلب نص الرابط الدائم حسب اللغة.
     */
    public function slug(
        ?string $locale = null
    ): ?string {
        return $this->permalink($locale)?->slug;
    }

    /**
     * جلب عنوان الخدمة حسب لغة الموقع.
     */
    public function localizedTitle(
        ?string $locale = null
    ): string {
        $locale ??= app()->getLocale();

        if (
            $locale === 'en' &&
            filled($this->title_en)
        ) {
            return $this->title_en;
        }

        return $this->title_ar;
    }

    /**
     * جلب وصف الخدمة حسب لغة الموقع.
     */
    public function localizedDescription(
        ?string $locale = null
    ): string {
        $locale ??= app()->getLocale();

        if (
            $locale === 'en' &&
            filled($this->description_en)
        ) {
            return $this->description_en;
        }

        return $this->description_ar;
    }

    public function hasPrice(): bool
    {
        return $this->price_from !== null;
    }

    /**
     * السعر بالريال العُماني: «2.50 ر.ع» / «OMR 2.50»، والعدد الصحيح بدون كسور.
     */
    public function formattedPrice(?string $locale = null): ?string
    {
        if (! $this->hasPrice()) {
            return null;
        }

        $locale ??= app()->getLocale();
        $amount = (float) $this->price_from;
        $number = fmod($amount, 1.0) === 0.0
            ? number_format($amount, 0)
            : number_format($amount, 2);

        return $locale === 'en' ? "OMR {$number}" : "{$number} ر.ع";
    }

    /**
     * «يبدأ من 10 ر.ع» للسعر الابتدائي، أو السعر وحده إذا كان ثابتًا.
     */
    public function priceLabel(?string $locale = null): ?string
    {
        $price = $this->formattedPrice($locale);

        if ($price === null) {
            return null;
        }

        return $this->price_is_starting
            ? __('services.pricing.starts_from', ['price' => $price], $locale)
            : $price;
    }

    public function localizedPriceUnit(?string $locale = null): ?string
    {
        return $this->localizedPriceField('price_unit', $locale);
    }

    public function localizedPriceNote(?string $locale = null): ?string
    {
        return $this->localizedPriceField('price_note', $locale);
    }

    private function localizedPriceField(string $field, ?string $locale): ?string
    {
        $locale ??= app()->getLocale();

        // الصفحة الإنجليزية ما تعرض نص عربي إذا ما فيه ترجمة.
        $value = $this->{$field.'_'.($locale === 'en' ? 'en' : 'ar')};

        return filled($value) ? $value : null;
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class)
            ->orderBy('sort_order')
            ->latest('id');
    }

    /**
     * جميع التقييمات (Reviews) لهذه الخدمة.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * التقييمات المعتمدة فقط، من الأحدث للأقدم.
     */
    public function approvedReviews(): MorphMany
    {
        return $this->reviews()
            ->approved()
            ->latest();
    }
}
