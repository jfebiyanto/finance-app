<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            //
        });
        DB::statement("ALTER TABLE debts MODIFY COLUMN payment_term ENUM('weekly', 'biweekly', 'monthly', 'yearly') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE debts MODIFY COLUMN payment_term ENUM('weekly', 'biweekly', 'monthly') NULL");
    }
};
