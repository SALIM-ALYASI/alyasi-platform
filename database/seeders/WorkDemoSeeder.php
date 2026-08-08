<?php

namespace Database\Seeders;

use App\Models\Work;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkDemoSeeder extends Seeder
{
    /**
     * نماذج تصميم توضيحية لقسم "أعمالي" بالصفحة الرئيسية، لحين توفر مشاريع
     * عملاء حقيقية كافية. كل سجل مُعلَّم بوضوح بوصفه "نموذج تصميم توضيحي"
     * عشان ما يُفهم غلط كمشروع عميل فعلي منجز.
     */
    public function run(): void
    {
        $works = [

            [
                'title' => 'تطبيق توصيل',
                'type' => 'design',
                'short_description' => 'نموذج تصميم توضيحي لتطبيق توصيل — واجهات طلب، تتبع، وتفاصيل الطلب.',
                'cover_image' => 'images/home/work-delivery-app.webp',
            ],

            [
                'title' => 'هوية بصرية لشركة تقنية',
                'type' => 'design',
                'short_description' => 'نموذج تصميم توضيحي لهوية بصرية متكاملة (شعار، قرطاسية، دليل استخدام).',
                'cover_image' => 'images/home/work-technova-brand.webp',
            ],

            [
                'title' => 'لوحة تحكم SaaS',
                'type' => 'design',
                'short_description' => 'نموذج تصميم توضيحي للوحة تحكم منتج SaaS — تحليلات، طلبات، وتقارير.',
                'cover_image' => 'images/home/work-managex-saas.webp',
            ],

        ];

        foreach ($works as $index => $work) {

            Work::updateOrCreate(

                [
                    'slug' => Str::slug($work['title']),
                ],

                [
                    'title' => $work['title'],
                    'type' => $work['type'],
                    'short_description' => $work['short_description'],
                    'cover_image' => $work['cover_image'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]

            );

        }
    }
}
