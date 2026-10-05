<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conference_recordings', function (Blueprint $table) {
            $table->string('prepared_path', 500)->nullable()->after('original_path');
            $table->string('agent_status', 20)->nullable()->after('job_error');
            $table->timestamp('agent_claimed_at')->nullable()->after('agent_status');
            $table->longText('transcript')->nullable()->after('agent_claimed_at');
            $table->string('transcript_language', 10)->nullable()->after('transcript');
            $table->string('transcript_model', 200)->nullable()->after('transcript_language');
            $table->float('duration_seconds')->nullable()->after('transcript_model');
            $table->boolean('transcript_synced')->default(false)->after('duration_seconds');

            $table->index(['agent_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('conference_recordings', function (Blueprint $table) {
            $table->dropIndex(['agent_status', 'created_at']);
            $table->dropColumn([
                'prepared_path',
                'agent_status',
                'agent_claimed_at',
                'transcript',
                'transcript_language',
                'transcript_model',
                'duration_seconds',
                'transcript_synced',
            ]);
        });
    }
};
