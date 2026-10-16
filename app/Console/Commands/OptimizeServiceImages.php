<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Support\Images\WebpOptimizer;
use Illuminate\Console\Command;

class OptimizeServiceImages extends Command
{
    protected $signature = 'images:optimize-services {--dry-run : عرض الأحجام بدون تعديل}';

    protected $description = 'تحويل صور الخدمات الثقيلة إلى WebP (مع نسخة جوال) وتحديث مساراتها؛ الأصل يبقى.';

    public function handle(): int
    {
        foreach (Service::query()->orderBy('id')->get() as $service) {
            $path = (string) $service->image;

            if ($path === '' || str_ends_with(strtolower($path), '.webp') || ! is_file(public_path($path))) {
                continue;
            }

            $before = filesize(public_path($path));

            if ($this->option('dry-run')) {
                $this->line("#{$service->id} {$path} ".round($before / 1024).'KB');

                continue;
            }

            $webp = WebpOptimizer::optimize($path);
            $service->forceFill(['image' => $webp])->save();

            $this->info(sprintf(
                '#%d %s: %dKB → %dKB (جوال %dKB)',
                $service->id,
                basename($path),
                $before / 1024,
                filesize(public_path($webp)) / 1024,
                filesize(public_path(preg_replace('/\.webp$/', '-'.WebpOptimizer::SMALL_WIDTH.'.webp', $webp))) / 1024,
            ));
        }

        return self::SUCCESS;
    }
}
