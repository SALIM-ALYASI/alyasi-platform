<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conference_recordings', function (Blueprint $table) {
            $table->string('upload_key', 64)->nullable()->after('relay_id');
            $table->unsignedInteger('chunks_received')->default(0)->after('size_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('conference_recordings', function (Blueprint $table) {
            $table->dropColumn(['upload_key', 'chunks_received']);
        });
    }
};
