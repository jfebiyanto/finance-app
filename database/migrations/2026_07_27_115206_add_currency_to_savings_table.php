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
        Schema::table('savings', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('interest_rate');
            $table->decimal('exchange_rate', 15, 2)->nullable()->after('currency');
            $table->decimal('amount_invested_foreign', 15, 2)->nullable()->after('exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'amount_invested_foreign']);
        });
    }
};
