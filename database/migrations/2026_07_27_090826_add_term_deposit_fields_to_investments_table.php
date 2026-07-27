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
            $table->string('bank_name')->nullable()->after('type');
            $table->integer('term_months')->nullable()->after('bank_name');
            $table->decimal('interest_rate', 5, 2)->nullable()->after('term_months');
            $table->date('maturity_date')->nullable()->after('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'term_months', 'interest_rate', 'maturity_date']);
        });
    }
};
