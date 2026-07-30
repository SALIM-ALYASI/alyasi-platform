<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image');
            $table->string('alt_text')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'work_id',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_images');
    }
};