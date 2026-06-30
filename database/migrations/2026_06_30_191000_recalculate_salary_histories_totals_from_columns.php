<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('salary_histories')) {
            return;
        }

        DB::table('salary_histories')
            ->orderBy('id')
            ->chunkById(200, function ($histories): void {
                foreach ($histories as $history) {
                    $totalAllowance = 0.0;
                    $totalDeduction = 0.0;

                    for ($index = 1; $index <= 50; $index++) {
                        $allowanceColumn = 'A' . $index;
                        $deductionColumn = 'D' . $index;

                        $totalAllowance += (float) ($history->$allowanceColumn ?? 0);
                        $totalDeduction += (float) ($history->$deductionColumn ?? 0);
                    }

                    $grossPay = round((float) $history->basic_salary + (float) $history->salary_areas + $totalAllowance, 2);
                    $netPay = round($grossPay - $totalDeduction, 2);
                    $nhis = round((0.5 / 100) * $grossPay, 2);
                    $employerPension = round((10 / 100) * $grossPay, 2);

                    DB::table('salary_histories')
                        ->where('id', $history->id)
                        ->update([
                            'total_allowance' => round($totalAllowance, 2),
                            'total_deduction' => round($totalDeduction, 2),
                            'gross_pay' => $grossPay,
                            'net_pay' => $netPay,
                            'nhis' => $nhis,
                            'employer_pension' => $employerPension,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // One-way data repair migration.
    }
};
