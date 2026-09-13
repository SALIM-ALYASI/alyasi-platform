<?php

namespace App\Console\Commands;

use App\Services\OfficialGulfExchangeRateService;
use Illuminate\Console\Command;
use Throwable;

class RefreshGulfExchangeRates extends Command
{
    protected $signature = 'fx:refresh-gulf-rates';

    protected $description = 'Refresh official Gulf exchange rates that are not permanently pegged';

    public function handle(OfficialGulfExchangeRateService $rates): int
    {
        try {
            $kwd = $rates->refreshKwd();
        } catch (Throwable $exception) {
            $this->error('تعذر تحديث سعر الدينار الكويتي من بنك الكويت المركزي.');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'KWD updated: 1 USD = %.6f KWD (%s)',
            $kwd['rate'],
            $kwd['rate_date'] ?? 'unknown date',
        ));

        return self::SUCCESS;
    }
}
