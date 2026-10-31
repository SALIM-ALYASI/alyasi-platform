<?php

namespace App\Support\Voice;

/**
 * تغطية الحروف في «صوتي»: كم مرة نطق سالم كل حرف عربي في نصوص أكّدها،
 * وترتيب جمل «اقرأ وسجّل» بحيث تجي أول الجمل اللي فيها الحروف الناقصة.
 * النموذج يتعلّم نطقه لكل حرف من أمثلة كثيرة لنفس الحرف بصوته.
 */
class LetterCoverage
{
    /** كم مرة يحتاج كل حرف يتسجّل بصوته قبل ما نعتبره مغطّى. */
    public const TARGET = 20;

    public const LETTERS = ['ء', 'ا', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي'];

    /** أشكال الحرف الواحد (الهمزات، التاء المربوطة، الألف المقصورة). */
    private const FORMS = ['أ' => 'ء', 'إ' => 'ء', 'ؤ' => 'ء', 'ئ' => 'ء', 'آ' => 'ء', 'ٱ' => 'ا', 'ة' => 'ت', 'ى' => 'ا'];

    /** @return array<string,int> عدد مرات كل حرف في النص */
    public static function count(string $text): array
    {
        $counts = array_fill_keys(self::LETTERS, 0);
        foreach (mb_str_split($text) as $ch) {
            $ch = self::FORMS[$ch] ?? $ch;
            if (isset($counts[$ch])) {
                $counts[$ch]++;
            }
        }

        return $counts;
    }

    /** @param iterable<string> $texts */
    public static function totals(iterable $texts): array
    {
        $totals = array_fill_keys(self::LETTERS, 0);
        foreach ($texts as $text) {
            foreach (self::count((string) $text) as $letter => $n) {
                $totals[$letter] += $n;
            }
        }

        return $totals;
    }

    /**
     * ترتيب جشِع: كل مرة نختار الجملة اللي تسد أكثر نقص في الحروف (بعد حساب
     * الجمل اللي قبلها)، فالحروف النادرة مثل ظ وث وغ تجي بدري بدل آخر القائمة.
     *
     * @param  list<string>  $sentences
     * @param  array<string,int>  $have
     * @return list<string>
     */
    public static function rank(array $sentences, array $have): array
    {
        $have = array_merge(array_fill_keys(self::LETTERS, 0), $have);
        $pool = [];
        foreach ($sentences as $i => $sentence) {
            $pool[$i] = self::count($sentence);
        }

        $ordered = [];
        // أول 80 جملة تكفي للتغطية؛ الحد يخلّي الطلب سريع مع 400 جملة.
        while ($pool && count($ordered) < 80) {
            $best = null;
            $bestScore = -1.0;
            foreach ($pool as $i => $counts) {
                $score = 0.0;
                foreach ($counts as $letter => $n) {
                    $missing = self::TARGET - $have[$letter];
                    if ($n > 0 && $missing > 0) {
                        // الحرف الأبعد عن الهدف يوزن أكثر.
                        $score += min($n, $missing) * ($missing / self::TARGET);
                    }
                }
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $i;
                }
            }
            if ($bestScore <= 0) {
                break; // كل الحروف مغطّاة: الباقي بترتيبه الأصلي.
            }
            foreach ($pool[$best] as $letter => $n) {
                $have[$letter] += $n;
            }
            $ordered[] = $sentences[$best];
            unset($pool[$best]);
        }
        foreach ($pool as $i => $_) {
            $ordered[] = $sentences[$i];
        }

        return array_values(array_unique($ordered));
    }

    /** @return list<array{letter:string,count:int}> */
    public static function report(array $have): array
    {
        return array_map(fn ($l) => ['letter' => $l, 'count' => (int) ($have[$l] ?? 0)], self::LETTERS);
    }
}
