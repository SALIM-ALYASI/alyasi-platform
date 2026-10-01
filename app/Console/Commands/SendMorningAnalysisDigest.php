<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use App\Support\N8nGmail;
use Illuminate\Console\Command;

/**
 * إيميل صباحي (٥ ص بتوقيت عُمان) بالأخبار المنشورة اللي ما زالت محتاجة
 * تحليل بشري (analysis_status الافتراضي "none" -- التحليل صار يدويًا
 * بالكامل بدل Gemini، انظر stage_6_analysis بـnews-bot-v2).
 *
 * الرد على هذا الإيميل (عبر workflow n8n منفصل يراقب الوارد) هو ما يحدّث
 * تحليل كل خبر فعليًا -- انظر AnalysisIngestController::ingestReply.
 */
class SendMorningAnalysisDigest extends Command
{
    protected $signature = 'analysis:send-digest';

    protected $description = 'يرسل إيميل صباحي بالأخبار المحتاجة تحليلاً بشريًا';

    public function handle(): int
    {
        $articles = NewsArticle::query()
            ->with('permalinks')
            ->published()
            ->where('analysis_status', 'none')
            ->whereDate('published_at', '>=', now()->subDay())
            ->latestPublished()
            ->get();

        if ($articles->isEmpty()) {
            return self::SUCCESS;
        }

        $lines = [
            'أخبار اليوم المحتاجة تحليلك ('.$articles->count().'):',
            '',
        ];

        foreach ($articles as $article) {
            $permalink = $article->permalinks->firstWhere('locale', 'ar')
                ?? $article->permalinks->first();

            $lines[] = "[#{$article->id}] {$article->title_ar}";
            $lines[] = 'الرابط: '.($permalink?->url() ?? '');
            $lines[] = 'تحليلك: ';
            $lines[] = '';
        }

        $lines[] = '[#END]';
        $lines[] = '---';
        $lines[] = 'رد على هذا الإيميل وعبّي "تحليلك:" تحت كل خبر، وسيب رقم [#...] زي ما هو (بما فيه [#END] -- علامة نهاية لازم تبقى بآخر الرد).';

        $subject = 'أخبار اليوم للتحليل -- '.now()->timezone('Asia/Muscat')->translatedFormat('d F Y');
        $body = implode("\n", $lines);

        $recipients = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.n8n.analysis_digest_recipients'))
        ));

        foreach ($recipients as $recipient) {
            N8nGmail::send($recipient, $subject, $body);
        }

        return self::SUCCESS;
    }
}
