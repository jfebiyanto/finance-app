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
        Schema::table('investments', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('avg_cost');
            $table->decimal('exchange_rate', 15, 2)->nullable()->after('currency');
            $table->decimal('amount_invested_foreign', 15, 2)->nullable()->after('exchange_rate');
            $table->decimal('current_value_foreign', 15, 2)->nullable()->after('current_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'amount_invested_foreign', 'current_value_foreign']);
        });
    }
};
