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
        Schema::table('states', function (Blueprint $table) {
            if (!Schema::hasColumn('states', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('states', 'country')) {
                $table->integer('country')->default(1);
            }
            if (!Schema::hasColumn('states', 'status')) {
                $table->tinyInteger('status')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn(['name', 'country', 'status']);
        });
    }
};
