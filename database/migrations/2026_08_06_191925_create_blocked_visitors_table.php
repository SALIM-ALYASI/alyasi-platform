<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_visitors', function (Blueprint $table) {
            $table->id();

            $table->string('device_token', 64)->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->string('reason', 255)->nullable();

            $table->timestamps();

            $table->index('device_token');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_visitors');
    }
};
