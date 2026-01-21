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
        // Add timestamps to states table if they don't exist
        if (!Schema::hasColumn('states', 'created_at')) {
            Schema::table('states', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        // Add timestamps to local_govts table if they don't exist
        if (!Schema::hasColumn('local_govts', 'created_at')) {
            Schema::table('local_govts', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('local_govts', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
