<?php

namespace App\Console\Commands;

use App\Support\AppStore\DeveloperApps;
use Illuminate\Console\Command;

class SyncAppStoreApps extends Command
{
    protected $signature = 'apps:sync-app-store';

    protected $description = 'تحديث قائمة تطبيقات المطوّر من App Store (الاسم والإصدار والتقييم والأيقونة)';

    public function handle(): int
    {
        $this->info('تطبيقات محدّثة: '.DeveloperApps::sync());

        return self::SUCCESS;
    }
}
