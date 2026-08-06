<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('community_post_id')
                ->constrained('community_posts')
                ->cascadeOnDelete();

            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->text('body');

            $table->boolean('followed_confirmation')->default(false);

            /*
             * حالة التعليق:
             * pending  = بانتظار المراجعة
             * approved = معتمد وظاهر للعامة
             * rejected = مرفوض
             */
            $table->string('status', 30)->default('pending');

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['community_post_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_comments');
    }
};
