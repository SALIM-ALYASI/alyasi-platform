<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_availability', function (Blueprint $table) {
            $table->id();

            $table->string('entity');
            $table->string('entity_type'); // device | service | api

            $table->enum('status', [
                'available',
                'gray_market',
                'geo_blocked',
                'unknown',
            ]);

            $table->boolean('has_local_warranty')->default(false);
            $table->string('local_reseller')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('verified_at');

            $table->timestamps();

            $table->index('entity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_availability');
    }
};
