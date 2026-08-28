<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_editions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('year');

            $table->string('title_ar', 255);
            $table->string('title_en', 255)->nullable();

            /*
             * نوع التغطية:
             * global_remote = مؤتمرات عالمية (آبل، سامسونج) - بث ومصادر رسمية
             * gulf_analysis = تحليل مقارن (GITEX، LEAP) - مصادر متعددة
             * local_field   = حضور ميداني (مسقط)
             */
            $table->string('coverage_type', 30)->default('global_remote');

            /*
             * هل المحرر حضر فعلياً؟ يتحكم بصيغة الكتابة المسموحة (قيد ناقد
             * لاحق) - true يسمح بصيغة الشاهد، false يمنعها منعاً باتاً.
             */
            $table->boolean('attended')->default(false);

            /*
             * حالة تأكيد الموعد:
             * confirmed = مؤكد رسمياً
             * expected  = متوقع (تسريبات/تقديرات)
             * unknown   = غير معروف بعد
             */
            $table->string('date_status', 20)->default('unknown');

            $table->dateTime('event_start_at')->nullable();
            $table->dateTime('event_end_at')->nullable();

            $table->string('livestream_url', 1000)->nullable();
            $table->string('image')->nullable();

            $table->string('short_description_ar', 500)->nullable();
            $table->string('short_description_en', 500)->nullable();

            /*
             * محتوى مرحلة concluded - شكل JSON مرن لحد ما تتحدد الحقول
             * النهائية أثناء إدخال بيانات آبل الفعلية (كل عنصر إعلان
             * بدرجة تأكيد، صف بجدول الأسعار: منتج/سعر رسمي/تحويل OMR).
             */
            $table->json('announcements')->nullable();
            $table->json('pricing_table')->nullable();

            /*
             * حكم الترقية (بس بمرحلة concluded):
             * yes / no / specific_segment
             */
            $table->string('upgrade_verdict', 30)->nullable();
            $table->text('upgrade_verdict_text')->nullable();

            /*
             * حالة النشر:
             * draft     = مسودة
             * published = منشور
             */
            $table->string('status', 30)->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['event_id', 'year']);
            $table->index('coverage_type');
            $table->index('date_status');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_editions');
    }
};
