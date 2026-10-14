<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPromotion extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_number',
        'salary_structure',
        'level',
        'step',
        'status',
        'arrears_months',
        'type'
    ];

    /**
     * A record is a conversion when the staff is being moved to a different
     * salary structure (e.g. CONPCASS -> CONUASS), otherwise it is a promotion.
     */
    public static function detectType($payrollNumber, $salaryStructureId): string
    {
        $employee = EmployeeProfile::where('payroll_number', $payrollNumber)->first();

        if ($employee && (string) $employee->salary_structure !== (string) $salaryStructureId) {
            return 'conversion';
        }

        return 'promotion';
    }

    public function isConversion(): bool
    {
        return $this->type === 'conversion';
    }
}
