<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technology_work', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('technology_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'work_id',
                'technology_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technology_work');
    }
};
