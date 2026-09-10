<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->unique()->after('name');
        });

        $used = [];

        foreach (DB::table('events')->orderBy('id')->get(['id', 'name']) as $event) {
            $name = (string) $event->name;
            $normalized = mb_strtolower($name);

            $base = match (true) {
                str_contains($normalized, 'apple'), str_contains($normalized, 'آبل'), str_contains($normalized, 'ابل') => 'apple',
                str_contains($normalized, 'samsung'), str_contains($normalized, 'سامسونج') => 'samsung',
                str_contains($normalized, 'huawei'), str_contains($normalized, 'هواوي') => 'huawei',
                str_contains($normalized, 'google'), str_contains($normalized, 'جوجل'), str_contains($normalized, 'غوغل') => 'google',
                str_contains($normalized, 'microsoft'), str_contains($normalized, 'مايكروسوفت') => 'microsoft',
                str_contains($normalized, 'comex') => 'comex-oman',
                default => Str::slug($name),
            };

            if ($base === '') {
                $base = 'event-'.$event->id;
            }

            $slug = $base;
            $counter = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$counter;
                $counter++;
            }

            $used[$slug] = true;

            DB::table('events')
                ->where('id', $event->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
