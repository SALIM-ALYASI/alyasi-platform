<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * قاموس «صوتي» الشخصي:
 * - term: كلمة/اسم يستخدمه سالم كثير، يتعطى لـ Whisper قبل التحويل.
 * - correction: خطأ متكرر (wrong) يتبدّل بالصحيح (right) بعد التحويل.
 */
class VoiceDictionaryEntry extends Model
{
    public const TERM = 'term';

    public const CORRECTION = 'correction';

    protected $fillable = ['kind', 'language', 'wrong', 'right', 'uses'];

    protected function casts(): array
    {
        return ['uses' => 'integer'];
    }

    /** يضيف أو يزيد عدد الاستخدام (نفس التصحيح مرة ثانية = أهم). */
    public static function remember(string $kind, string $language, string $wrong, string $right): ?self
    {
        $wrong = self::clean($wrong);
        $right = self::clean($right);
        if ($right === '' || ($kind === self::CORRECTION && ($wrong === '' || $wrong === $right))) {
            return null;
        }

        $entry = self::firstOrNew(['kind' => $kind, 'language' => $language, 'wrong' => $kind === self::TERM ? '' : $wrong, 'right' => $right]);
        $entry->uses = $entry->exists ? $entry->uses + 1 : 1;
        $entry->save();

        return $entry;
    }

    private static function clean(string $value): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', $value) ?? ''), 0, 120);
    }

    /** كلمات للنموذج: الأكثر استخدامًا أول، بحد ~600 حرف (حد prompt في Whisper تقريبًا 224 token). */
    public static function promptFor(string $language): string
    {
        $base = $language === 'en'
            ? 'Salem Al Hajri, ALYASI, alyasi.dev, Oman, Muscat.'
            : 'سالم الحجري، منصة الياسي، عُمان، مسقط.';

        $terms = self::where('language', $language)
            ->orderByDesc('uses')->orderByDesc('updated_at')
            ->limit(150)->pluck('right')->unique();

        $out = $base;
        foreach ($terms as $term) {
            if (mb_strlen($out) + mb_strlen($term) + 2 > 300) {
                break;
            }
            $out .= ($language === 'en' ? ', ' : '، ').$term;
        }

        // جمل أكّدها سالم (الأحدث آخر، لأن آخر الـ prompt أقوى أثر): النموذج يتعوّد
        // على كلامه وأسلوبه، مب بس على كلمات منفصلة.
        $sentences = VoiceNote::whereNotNull('approved_at')->where('language', $language)
            ->whereNotNull('edited_text')->latest('approved_at')->limit(20)->pluck('edited_text')
            ->map(fn ($t) => trim(preg_replace('/\s+/u', ' ', (string) $t) ?? ''))
            ->filter(fn ($t) => $t !== '' && mb_strlen($t) <= 200)->values();
        $tail = '';
        foreach ($sentences as $sentence) {
            if (mb_strlen($out) + mb_strlen($tail) + mb_strlen($sentence) + 1 > 700) {
                break;
            }
            $tail = $sentence.' '.$tail;
        }

        return trim($out.' '.$tail);
    }

    /** @return list<array{0: string, 1: string}> */
    public static function correctionsFor(string $language): array
    {
        return self::where('kind', self::CORRECTION)->where('language', $language)
            ->get(['wrong', 'right'])
            ->map(fn (self $e) => [$e->wrong, $e->right])
            ->sortByDesc(fn ($pair) => mb_strlen($pair[0]))
            ->values()->all();
    }
}
