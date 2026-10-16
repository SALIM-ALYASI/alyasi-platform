<?php

namespace App\Console\Commands;

use App\Models\EventEdition;
use App\Support\Events\OfficialDateChecker;
use Illuminate\Console\Command;

class CheckOfficialEventDates extends Command
{
    protected $signature = 'events:check-official-dates';

    protected $description = 'مقارنة مواعيد المؤتمرات القادمة بمواقعها الرسمية وتحديثها عند التغيير.';

    public function handle(): int
    {
        EventEdition::query()
            ->whereNotNull('official_url')
            ->where('event_start_at', '>', now())
            ->each(fn (EventEdition $edition) => $this->line('#'.$edition->id.' '.$edition->title_ar.': '.OfficialDateChecker::check($edition)));

        return self::SUCCESS;
    }
}
