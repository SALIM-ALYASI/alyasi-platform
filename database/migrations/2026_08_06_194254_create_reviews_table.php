<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->string('reviewable_type', 60);
            $table->unsignedBigInteger('reviewable_id');

            $table->string('name', 255);
            $table->string('email', 255)->nullable();

            $table->unsignedTinyInteger('rating');
            $table->text('body');

            /*
             * حالة التقييم:
             * pending  = بانتظار المراجعة
             * approved = معتمد وظاهر للعامة
             * rejected = مرفوض
             */
            $table->string('status', 30)->default('pending');

            $table->string('ip_address', 45)->nullable();
            $table->string('device_token', 64)->nullable();

            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id', 'status']);
            $table->index('status');
            $table->index('device_token');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
