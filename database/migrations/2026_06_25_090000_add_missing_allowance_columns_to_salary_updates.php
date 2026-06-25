<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_updates', function (Blueprint $table): void {
            for ($index = 15; $index <= 50; $index++) {
                $column = 'A'.$index;

                if (! Schema::hasColumn('salary_updates', $column)) {
                    $table->double($column)->nullable()->default(0.00);
                }
            }
        });

        if (Schema::hasTable('salary_histories')) {
            Schema::table('salary_histories', function (Blueprint $table): void {
                for ($index = 15; $index <= 50; $index++) {
                    $column = 'A'.$index;

                    if (! Schema::hasColumn('salary_histories', $column)) {
                        $table->double($column)->nullable()->default(0.00);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('salary_updates', function (Blueprint $table): void {
            for ($index = 15; $index <= 50; $index++) {
                $column = 'A'.$index;

                if (Schema::hasColumn('salary_updates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('salary_histories')) {
            Schema::table('salary_histories', function (Blueprint $table): void {
                for ($index = 15; $index <= 50; $index++) {
                    $column = 'A'.$index;

                    if (Schema::hasColumn('salary_histories', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
