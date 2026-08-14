<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->string('source')->default('upload')->after('job_id');
            $table->string('status')->nullable()->after('source');
            $table->json('smart_content_data')->nullable()->after('platforms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->dropColumn(['source', 'status', 'smart_content_data']);
        });
    }
};
