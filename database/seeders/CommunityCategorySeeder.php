<?php

namespace Database\Seeders;

use App\Models\CommunityCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [

            [
                'name' => 'المبادرات',
                'description' => 'المبادرات المجتمعية والتقنية.',
                'icon' => 'fa-solid fa-lightbulb',
            ],

            [
                'name' => 'الفعاليات',
                'description' => 'الفعاليات والمؤتمرات وورش العمل.',
                'icon' => 'fa-solid fa-calendar-days',
            ],

            [
                'name' => 'الإعلانات',
                'description' => 'الإعلانات الرسمية والمجتمعية.',
                'icon' => 'fa-solid fa-bullhorn',
            ],

            [
                'name' => 'قصص النجاح',
                'description' => 'قصص وتجارب ملهمة.',
                'icon' => 'fa-solid fa-award',
            ],

            [
                'name' => 'المشاركات',
                'description' => 'مشاركات أعضاء المجتمع.',
                'icon' => 'fa-solid fa-users',
            ],

        ];

        foreach ($categories as $index => $category) {

            CommunityCategory::updateOrCreate(

                [
                    'slug' => Str::slug($category['name']),
                ],

                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]

            );

        }
    }
}