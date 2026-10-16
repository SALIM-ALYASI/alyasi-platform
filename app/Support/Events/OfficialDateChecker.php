<?php

namespace App\Support\Events;

use App\Models\EventEdition;
use App\Support\WhatsAppAlerts;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * يتحقق من موعد المؤتمر بموقعه الرسمي (مرة باليوم): إذا تغيّر نطاق التاريخ
 * («1 – 4 February 2027») يحدّث الموعد ويرسل لسالم تنبيه بالقديم والجديد.
 * الساعات المحفوظة تبقى كما هي؛ يتغيّر اليوم بس.
 */
final class OfficialDateChecker
{
    private const MONTHS = [
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
        'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8,
        'sep' => 9, 'sept' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    /**
     * يشغّل الفحص بعد الاستجابة إذا مر يوم على آخر فحص (بدون جدولة سيرفر).
     */
    public static function checkLaterIfDue(EventEdition $edition): void
    {
        if (blank($edition->official_url) || ! $edition->event_start_at || $edition->event_start_at->isPast()) {
            return;
        }

        if ($edition->official_checked_at && $edition->official_checked_at->gt(now()->subDay())) {
            return;
        }

        if (! Cache::add('official-date-check:'.$edition->id, true, now()->addHour())) {
            return;
        }

        dispatch(fn () => self::check($edition->fresh()))->afterResponse();
    }

    /**
     * @return string ملاحظة الفحص (تنحفظ بالنسخة).
     */
    public static function check(EventEdition $edition): string
    {
        try {
            $html = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ALYASI-EventCheck/1.0; +https://alyasi.dev)'])
                ->get($edition->official_url)
                ->throw()
                ->body();
        } catch (\Throwable $exception) {
            Log::warning('official_date_check_fetch_failed', ['edition' => $edition->id, 'error' => $exception->getMessage()]);

            return self::save($edition, 'تعذّر فتح الموقع الرسمي');
        }

        // كل وسم يصير مسافة، عشان ما تلتصق «2027» بـ«8» من العنصر اللي بعده.
        $text = html_entity_decode(preg_replace('/<(script|style)\b.*?<\/\1>|<[^>]+>/is', ' ', $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $range = self::extractRange($text, (int) $edition->year);

        if ($range === null) {
            return self::save($edition, 'ما لقينا تاريخ واضح بالموقع الرسمي');
        }

        [$start, $end] = $range;
        $tz = 'Asia/Muscat';
        $oldStart = $edition->event_start_at->copy()->timezone($tz);
        $oldEnd = ($edition->event_end_at ?? $edition->event_start_at)->copy()->timezone($tz);

        if ($oldStart->isSameDay($start) && $oldEnd->isSameDay($end)) {
            return self::save($edition, 'مطابق: '.$start->format('Y-m-d').' → '.$end->format('Y-m-d'));
        }

        $edition->event_start_at = $start->copy()->setTime($oldStart->hour, $oldStart->minute)->utc();
        $edition->event_end_at = $end->copy()->setTime($oldEnd->hour, $oldEnd->minute)->utc();
        $note = 'تغيّر الموعد: '.$oldStart->format('d.m.Y').'–'.$oldEnd->format('d.m.Y').' ← '.$start->format('d.m.Y').'–'.$end->format('d.m.Y');
        self::save($edition, $note);

        WhatsAppAlerts::send($edition->title_ar.': '.$note.' (حسب الموقع الرسمي '.$edition->official_url.'). حدّثنا الموعد بالموقع تلقائيًا.');

        return $note;
    }

    /**
     * «1 – 4 February 2027» أو «1 February - 4 February 2027» أو «28 Jan – 2 Feb 2027».
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public static function extractRange(string $text, int $year): ?array
    {
        $text = preg_replace('/\s+/u', ' ', str_replace(['\\-', '–', '—'], '-', $text));
        $month = '(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|June?|July?|Aug(?:ust)?|Sept?(?:ember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)';
        $tz = 'Asia/Muscat';

        // 28 Jan - 2 Feb 2027 / 1 February - 4 February 2027
        if (preg_match('/\b(\d{1,2}) '.$month.' ?- ?(\d{1,2}) '.$month.',? ('.$year.')\b/i', $text, $m)) {
            return [
                Carbon::create($year, self::month($m[2]), (int) $m[1], 0, 0, 0, $tz),
                Carbon::create($year, self::month($m[4]), (int) $m[3], 0, 0, 0, $tz),
            ];
        }

        // 1 - 4 February 2027
        if (preg_match('/\b(\d{1,2}) ?- ?(\d{1,2}) '.$month.',? ('.$year.')\b/i', $text, $m)) {
            return [
                Carbon::create($year, self::month($m[3]), (int) $m[1], 0, 0, 0, $tz),
                Carbon::create($year, self::month($m[3]), (int) $m[2], 0, 0, 0, $tz),
            ];
        }

        return null;
    }

    private static function month(string $name): int
    {
        return self::MONTHS[strtolower($name)] ?? 1;
    }

    private static function save(EventEdition $edition, string $note): string
    {
        $edition->official_checked_at = now();
        $edition->official_check_note = $note;
        $edition->saveQuietly();

        return $note;
    }
}
