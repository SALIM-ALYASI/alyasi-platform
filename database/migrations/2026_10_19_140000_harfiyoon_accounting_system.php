<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * «الحرفيون» نظام محاسبي (تصحيح سالم، 19 أكتوبر 2026) -- مب تطبيق جوال.
     * الاسم والرابط (/works/harfiyoon) ما يتغيرون.
     */
    public function up(): void
    {
        $work = DB::table('works')->where('slug', 'harfiyoon')->first();

        if (! $work) {
            return;
        }

        Storage::disk('local')->put('backups/harfiyoon-before-accounting-'.now()->format('YmdHis').'.json', json_encode($work, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $replace = fn (?string $value, array $map) => $value === null ? null : str_replace(array_keys($map), array_values($map), $value);

        DB::table('works')->where('id', $work->id)->update([
            'type' => 'accounting_system',
            'title' => $replace($work->title, ['منصة الحرفيون – نظام إدارة المبيعات والمخزون' => 'الحرفيون – نظام محاسبي لإدارة المبيعات والمخزون']),
            'title_en' => $replace($work->title_en, ['Sales & Inventory Management System' => 'Accounting System for Sales & Inventory']),
            'short_description' => $replace($work->short_description, ['نظام متكامل لإدارة المبيعات' => 'نظام محاسبي متكامل لإدارة المبيعات']),
            'short_description_en' => $replace($work->short_description_en, ['An integrated sales, inventory and point-of-sale (POS) system' => 'An integrated accounting system for sales, inventory and point of sale (POS)']),
            'description' => $replace($work->description, ['الحرفيون هو نظام ويب متكامل لإدارة الأنشطة التجارية' => 'الحرفيون نظام محاسبي متكامل على الويب لإدارة الأنشطة التجارية']),
            'description_en' => $replace($work->description_en, ['Al Harfiyoon is an integrated web system for running business operations' => 'Al Harfiyoon is an integrated web-based accounting system for running business operations']),
        ]);
    }

    public function down(): void
    {
        // القيم السابقة بالنسخة الاحتياطية (backups/harfiyoon-before-accounting-*.json).
    }
};
