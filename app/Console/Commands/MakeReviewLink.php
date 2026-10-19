<?php

namespace App\Console\Commands;

use App\Models\Work;
use App\Support\Reviews\ReviewLink;
use Illuminate\Console\Command;

/**
 * رابط مؤقت يرسله سالم لعميل مشروع يطلب تقييمه: php artisan reviews:link sultan-law --days=30
 */
class MakeReviewLink extends Command
{
    protected $signature = 'reviews:link {work : رابط المشروع (slug)} {--days=30 : صلاحية الرابط بالأيام}';

    protected $description = 'إنشاء رابط مؤقت (عربي وإنجليزي) لطلب تقييم عميل على مشروع';

    public function handle(): int
    {
        $work = Work::query()->where('slug', $this->argument('work'))->first();

        if (! $work) {
            $this->error('ما لقيت مشروع بهذا الرابط.');

            return self::FAILURE;
        }

        $expires = now()->addDays(max(1, (int) $this->option('days')));
        $links = ReviewLink::make($work->slug, $expires);

        $this->line('المشروع: '.$work->title);
        $this->line('ينتهي: '.$expires->timezone('Asia/Muscat')->format('Y-m-d H:i').' (مسقط)');
        $this->line('عربي:   '.$links['ar']);
        $this->line('English: '.$links['en']);

        return self::SUCCESS;
    }
}
