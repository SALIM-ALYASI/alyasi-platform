<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * رسائل التواصل غير المرتبطة بعقد تنحذف بعد 12 شهرًا من آخر تواصل
     * (سياسة الخصوصية). المرتبطة بعقد أو مشروع تُعلَّم يدويًا من لوحة التحكم.
     */
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->boolean('is_retained')->default(false)->after('status');
            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropIndex(['email', 'created_at']);
            $table->dropColumn('is_retained');
        });
    }
};
