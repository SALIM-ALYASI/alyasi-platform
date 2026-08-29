<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_slug_redirects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_id')
                ->constrained('works')
                ->cascadeOnDelete();

            $table->string('old_slug')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_slug_redirects');
    }
};
