<?php

namespace App\Console\Commands;

use App\Support\Events\InterviewIndex;
use Illuminate\Console\Command;

class SyncEventInterviews extends Command
{
    protected $signature = 'events:sync-interviews';

    protected $description = 'تحديث فهرس مقابلات المؤتمرات (event_interviews) من مصادر المقابلات';

    public function handle(): int
    {
        $this->info('مقابلات محدّثة: '.InterviewIndex::sync());

        return self::SUCCESS;
    }
}
