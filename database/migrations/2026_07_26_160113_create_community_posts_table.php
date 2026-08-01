<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('community_category_id')
                ->nullable()
                ->constrained('community_categories')
                ->nullOnDelete();

            $table->string('title', 255);
            $table->string('slug', 280)->unique();

            $table->string('short_description', 500);
            $table->longText('content')->nullable();

            /*
             * المبادرات والفعاليات والإعلانات وغيرها.
             *
             * initiative     = مبادرة
             * event          = فعالية
             * announcement   = إعلان
             * success_story  = قصة نجاح
             * community_post = مشاركة مجتمعية
             */
            $table->string('type', 50)->default('community_post');

            $table->string('image')->nullable();

            /*
             * بيانات اختيارية للفعاليات.
             */
            $table->string('location', 255)->nullable();
            $table->dateTime('event_start_at')->nullable();
            $table->dateTime('event_end_at')->nullable();
            $table->string('registration_url', 1000)->nullable();

            /*
             * حالة المحتوى:
             * draft     = مسودة
             * published = منشور
             * archived  = مؤرشف
             */
            $table->string('status', 30)->default('draft');

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('published_at');
            $table->index(['is_active', 'status']);
            $table->index(['is_featured', 'sort_order']);
            $table->index([
                'community_category_id',
                'is_active',
                'status',
            ], 'community_posts_category_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};
