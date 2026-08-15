<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->string('name_ar', 150)->nullable()->after('id');
            $table->string('name_en', 150)->nullable()->after('name_ar');
            $table->string('description_ar', 500)->nullable()->after('description');
            $table->string('description_en', 500)->nullable()->after('description_ar');
        });

        DB::table('article_categories')->orderBy('id')->chunkById(100, function ($categories) {
            foreach ($categories as $category) {
                DB::table('article_categories')->where('id', $category->id)->update([
                    'name_ar' => $category->name,
                    'description_ar' => $category->description,
                ]);
            }
        });

        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('article_categories', function (Blueprint $table) {
            $table->string('name_ar', 150)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            $table->string('name', 150)->nullable()->after('id');
            $table->string('description', 500)->nullable()->after('slug');
        });

        DB::table('article_categories')->orderBy('id')->chunkById(100, function ($categories) {
            foreach ($categories as $category) {
                DB::table('article_categories')->where('id', $category->id)->update([
                    'name' => $category->name_ar,
                    'description' => $category->description_ar,
                ]);
            }
        });

        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en']);
        });
    }
};
