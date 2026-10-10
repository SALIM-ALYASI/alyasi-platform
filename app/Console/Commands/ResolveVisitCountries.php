<?php

namespace App\Console\Commands;

use App\Models\PageVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * تعبئة بلدان الزيارات القديمة دفعة وحدة: كل عنوان IP مرة وحدة، عبر
 * واجهة ip-api الجماعية (100 عنوان بالطلب، 15 طلب بالدقيقة).
 */
class ResolveVisitCountries extends Command
{
    protected $signature = 'visits:resolve-countries {--max=5000 : أقصى عدد عناوين بهالتشغيل}';

    protected $description = 'تحديد بلدان الزيارات اللي ما انعرف بلدها (دفعات عبر ip-api batch)';

    public function handle(): int
    {
        $ips = PageVisit::query()
            ->whereNull('country_name')
            ->whereNotNull('ip_address')
            ->distinct()
            ->limit((int) $this->option('max'))
            ->pluck('ip_address');

        $public = $ips->filter(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
        $local = $ips->diff($public);

        if ($local->isNotEmpty()) {
            PageVisit::query()->whereIn('ip_address', $local)->whereNull('country_name')->update(['country_name' => 'محلي']);
        }

        $resolved = 0;

        foreach ($public->values()->chunk(100) as $i => $chunk) {
            if ($i > 0) {
                sleep(5); // نبقى تحت 15 طلب بالدقيقة
            }

            $response = Http::timeout(20)->post('http://ip-api.com/batch?fields=status,country,countryCode,query', $chunk->values()->all());

            if (! $response->successful()) {
                $this->warn('ip-api رد بـ '.$response->status().' -- نوقف ونكمل بتشغيل لاحق.');
                break;
            }

            foreach ($response->json() as $row) {
                if (($row['status'] ?? null) !== 'success') {
                    continue;
                }

                $resolved += PageVisit::query()
                    ->where('ip_address', $row['query'])
                    ->whereNull('country_name')
                    ->update(['country_code' => $row['countryCode'], 'country_name' => $row['country']]);
            }

            $this->line('دفعة '.($i + 1).': تم');
        }

        $this->info("تم تحديد البلد لـ {$resolved} زيارة ({$public->count()} عنوان).");

        return self::SUCCESS;
    }
}
