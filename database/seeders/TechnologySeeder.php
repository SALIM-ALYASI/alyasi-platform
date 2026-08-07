<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * القيم اللي كانت مكتوبة يدويًا (hardcoded) داخل home/integrations.blade.php
     * قبل ما تصير القسم يعرض تقنيات إدارة المنصة الحقيقية (جدول technologies).
     */
    public function run(): void
    {
        $technologies = [
            ['name' => 'Laravel', 'icon' => 'fa-brands fa-laravel'],
            ['name' => 'PHP 8', 'icon' => 'fa-brands fa-php'],
            ['name' => 'MySQL', 'icon' => 'fa-solid fa-database'],
            ['name' => 'JavaScript', 'icon' => 'fa-brands fa-js'],
            ['name' => 'REST API', 'icon' => 'fa-solid fa-network-wired'],
            ['name' => 'Redis', 'icon' => 'fa-solid fa-memory'],
            ['name' => 'GitHub', 'icon' => 'fa-brands fa-github'],
            ['name' => 'Vite', 'icon' => 'fa-solid fa-bolt'],
            ['name' => 'Docker', 'icon' => 'fa-brands fa-docker'],
            ['name' => 'Nginx', 'icon' => 'fa-solid fa-server'],
            ['name' => 'Linux', 'icon' => 'fa-brands fa-linux'],
            ['name' => 'الذكاء الاصطناعي', 'icon' => 'fa-solid fa-brain'],
            ['name' => 'تصميم الصور بالذكاء الاصطناعي', 'icon' => 'fa-solid fa-image'],
            ['name' => 'إنشاء الفيديوهات بالذكاء الاصطناعي', 'icon' => 'fa-solid fa-video'],
            ['name' => 'التصميم بالذكاء الاصطناعي', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
            ['name' => 'المساعدات الذكية', 'icon' => 'fa-solid fa-comments'],
            ['name' => 'الأتمتة الذكية', 'icon' => 'fa-solid fa-robot'],
        ];

        foreach ($technologies as $index => $technology) {

            Technology::updateOrCreate(

                [
                    'slug' => Str::slug($technology['name']),
                ],

                [
                    'name' => $technology['name'],
                    'icon' => $technology['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]

            );

        }
    }
}
