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
        Schema::table('debts', function (Blueprint $table) {
            $table->decimal('principal_amount', 15, 2)->nullable()->after('total_amount');
            $table->enum('payment_term', ['weekly', 'biweekly', 'monthly'])->nullable()->after('interest_rate');
            $table->integer('term_count')->nullable()->after('payment_term');
            $table->decimal('term_amount', 15, 2)->nullable()->after('term_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn(['principal_amount', 'payment_term', 'term_count', 'term_amount']);
        });
    }
};
