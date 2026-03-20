<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('annual_salary_increments', function (Blueprint $table) {
            if (!Schema::hasColumn('annual_salary_increments', 'arrears_months')) {
                $table->integer('arrears_months')->nullable()->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annual_salary_increments', function (Blueprint $table) {
            if (Schema::hasColumn('annual_salary_increments', 'arrears_months')) {
                $table->dropColumn('arrears_months');
            }
        });
    }
};
