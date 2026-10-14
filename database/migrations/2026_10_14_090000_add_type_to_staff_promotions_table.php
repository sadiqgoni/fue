<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('staff_promotions', function (Blueprint $table) {
            // 'promotion' = same salary structure, 'conversion' = moved to a new salary structure
            $table->string('type', 20)->default('promotion')->after('arrears_months');
        });
    }

    public function down(): void
    {
        Schema::table('staff_promotions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
