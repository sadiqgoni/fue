<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualSalaryIncrement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'increment_month',
        'increment_year',
        'month_year',
        'salary_structure',
        'grade_level',
        'old_grade_step',
        'new_grade_step',
        'status',
        'arrears_months'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }
}
