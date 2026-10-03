<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conference_recordings', function (Blueprint $table) {
            $table->id();
            $table->string('relay_id', 40)->unique();
            $table->string('status', 20)->default('received');
            $table->string('original_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('home_job_id', 40)->nullable();
            $table->string('job_status', 20)->nullable();
            $table->text('job_error')->nullable();
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_recordings');
    }
};
