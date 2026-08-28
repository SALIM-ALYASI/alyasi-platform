<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            /*
             * المؤتمر الدائم نفسه (زي "مؤتمر آبل") - تحته نسخة كل سنة
             * بجدول event_editions. تسمية داخلية/إدارية، مو عنوان معروض.
             */
            $table->string('name', 255);

            $table->string('organizer', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
