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
        Schema::create('marketers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone');
            $table->string('code', 9)->unique();

            // نسبة العمولة الأساسية، وتزيد تلقائيًا كل ما وصل عدد
            // العملاء اللي جابهم المسوّق مضاعف "customers_per_tier".
            $table->decimal('base_commission_rate', 5, 2)->default(5.00);
            $table->decimal('tier_increment_rate', 5, 2)->default(1.00);
            $table->unsignedInteger('customers_per_tier')->default(10);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketers');
    }
};
