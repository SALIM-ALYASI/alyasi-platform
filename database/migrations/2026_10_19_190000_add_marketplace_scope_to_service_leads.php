<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مسار ثاني لطلبات العملاء: مشاريع مستقل وخمسات من أي بلد (ميزانية 100$ فأكثر)
 * تُجمع وتوصل بقائمة يومية وحدة -- غير طلبات الخليج اللي توصل فورًا.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_leads', function (Blueprint $table): void {
            $table->string('scope', 20)->default('gcc')->after('agent')->index();
            $table->unsignedInteger('budget_usd')->nullable()->after('budget');
            $table->char('country', 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_leads', function (Blueprint $table): void {
            $table->dropColumn(['scope', 'budget_usd']);
        });
    }
};
