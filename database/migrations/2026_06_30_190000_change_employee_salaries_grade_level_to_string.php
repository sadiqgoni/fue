<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_salaries') && Schema::hasColumn('employee_salaries', 'grade_level')) {
            DB::statement('ALTER TABLE employee_salaries MODIFY grade_level VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_salaries') && Schema::hasColumn('employee_salaries', 'grade_level')) {
            DB::statement('ALTER TABLE employee_salaries MODIFY grade_level INT NULL');
        }
    }
};
