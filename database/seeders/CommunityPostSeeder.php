<?php

namespace Database\Seeders;

use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CommunityPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CommunityPost::truncate();

        $categories = CommunityCategory::all()->keyBy('name');

        $posts = [

            [
                'title' => 'إطلاق مجتمع ALYASI التقني',
                'category' => 'المبادرات',
                'type' => 'initiative',
                'featured' => true,
            ],

            [
                'title' => 'ورشة Laravel المتقدمة',
                'category' => 'الفعاليات',
                'type' => 'event',
                'featured' => true,
            ],

            [
                'title' => 'الإعلان عن برنامج المتطوعين',
                'category' => 'الإعلانات',
                'type' => 'announcement',
                'featured' => false,
            ],

            [
                'title' => 'قصة نجاح أحد أعضاء المجتمع',
                'category' => 'قصص النجاح',
                'type' => 'success_story',
                'featured' => true,
            ],

            [
                'title' => 'أفضل أدوات المطورين لعام 2026',
                'category' => 'المشاركات',
                'type' => 'community_post',
                'featured' => false,
            ],

        ];

        foreach ($posts as $index => $item) {

            $category = $categories[$item['category']];

            CommunityPost::create([

                'community_category_id' => $category->id,

                'title' => $item['title'],

                'slug' => Str::slug($item['title']),

                'short_description' => 'هذا مثال على منشور تجريبي داخل مجتمع ALYASI لاختبار تصميم الصفحة وصفحة التفاصيل.',

                'content' => '

<h2>مرحباً بكم في مجتمع ALYASI</h2>

<p>
هذا المحتوى تجريبي لغرض اختبار تصميم الصفحة.
</p>

<p>
يمكن استبداله لاحقاً بمحرر النصوص داخل لوحة التحكم.
</p>

<ul>
<li>مجتمع احترافي</li>
<li>فعاليات تقنية</li>
<li>ورش عمل</li>
<li>قصص نجاح</li>
</ul>

<p>
نسعى لبناء مجتمع عربي تقني يشارك المعرفة والخبرات بين الجميع.
</p>

',

                'type' => $item['type'],

                'image' => null,

                'location' => $item['type'] == 'event'
                    ? 'مسقط - سلطنة عمان'
                    : null,

                'event_start_at' => $item['type'] == 'event'
                    ? Carbon::now()->addDays(15)
                    : null,

                'event_end_at' => $item['type'] == 'event'
                    ? Carbon::now()->addDays(15)->addHours(4)
                    : null,

                'registration_url' => $item['type'] == 'event'
                    ? 'https://alyasi.com'
                    : null,

                'status' => 'published',

                'sort_order' => $index + 1,

                'is_featured' => $item['featured'],

                'is_active' => true,

                'published_at' => now()->subDays(rand(1, 20)),

            ]);

        }

        /*
        |------------------------------------------------------------
        | بيانات إضافية
        |------------------------------------------------------------
        */

        foreach (range(1, 15) as $i) {

            $category = CommunityCategory::inRandomOrder()->first();

            CommunityPost::create([

                'community_category_id' => $category->id,

                'title' => "منشور مجتمعي رقم {$i}",

                'slug' => "community-post-{$i}",

                'short_description' => 'منشور تجريبي لاختبار Pagination والبطاقات.',

                'content' => '<p>هذا محتوى تجريبي.</p>',

                'type' => collect([
                    'initiative',
                    'announcement',
                    'community_post',
                    'success_story',
                ])->random(),

                'status' => 'published',

                'sort_order' => $i + 10,

                'is_featured' => false,

                'is_active' => true,

                'published_at' => now()->subDays(rand(1, 90)),

            ]);

        }
    }
}
