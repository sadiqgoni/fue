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
        Schema::table('local_govts', function (Blueprint $table) {
            if (!Schema::hasColumn('local_govts', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('local_govts', 'state_id')) {
                $table->unsignedBigInteger('state_id');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            }
            if (!Schema::hasColumn('local_govts', 'status')) {
                $table->tinyInteger('status')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('local_govts', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn(['name', 'state_id', 'status']);
        });
    }
};
