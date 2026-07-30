<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->enum('type', [
                'website',
                'design',
                'advertisement',
                'video',
                'photography',
                'event',
                'personal',
                'other',
            ])->default('other');

            $table->string('short_description', 500);
            $table->longText('description')->nullable();

            $table->string('cover_image')->nullable();

            $table->string('client_name')->nullable();
            $table->string('work_url')->nullable();

            $table->date('completed_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
