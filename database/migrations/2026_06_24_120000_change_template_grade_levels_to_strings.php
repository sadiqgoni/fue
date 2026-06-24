<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE salary_allowance_templates SET Created_at = NULL WHERE Created_at = '0000-00-00 00:00:00'");
        DB::statement("UPDATE salary_allowance_templates SET Updated_at = NULL WHERE Updated_at = '0000-00-00 00:00:00'");
        DB::statement("UPDATE salary_deduction_templates SET Created_at = NULL WHERE Created_at = '0000-00-00 00:00:00'");
        DB::statement("UPDATE salary_deduction_templates SET Updated_at = NULL WHERE Updated_at = '0000-00-00 00:00:00'");

        DB::statement('ALTER TABLE salary_allowance_templates MODIFY grade_level_from VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE salary_allowance_templates MODIFY grade_level_to VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE salary_deduction_templates MODIFY grade_level_from VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE salary_deduction_templates MODIFY grade_level_to VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE salary_allowance_templates MODIFY grade_level_from INT NOT NULL');
        DB::statement('ALTER TABLE salary_allowance_templates MODIFY grade_level_to INT NOT NULL');
        DB::statement('ALTER TABLE salary_deduction_templates MODIFY grade_level_from INT NOT NULL');
        DB::statement('ALTER TABLE salary_deduction_templates MODIFY grade_level_to INT NOT NULL');
    }
};
