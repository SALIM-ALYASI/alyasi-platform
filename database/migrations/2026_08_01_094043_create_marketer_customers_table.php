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
        Schema::create('marketer_customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('marketer_id')
                ->constrained('marketers')
                ->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');

            $table->decimal('deal_amount', 10, 2)->nullable();

            // نسبة العمولة والمبلغ وقت تسجيل هذا العميل، محفوظة كـ "لقطة"
            // حتى ما تتغيّر عمولة عميل سابق لو تغيّرت شريحة المسوّق لاحقًا.
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketer_customers');
    }
};
