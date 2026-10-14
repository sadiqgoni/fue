<?php

namespace App\Imports;

use App\Models\SalaryStructure;
use App\Models\StaffPromotion;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class PromotionImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
//    public $data;
//    public function __construct($data)
//    {
//        $this->data=$data;
//    }

    public function model(array $row)
    {
        $ss=self::findStructure($row['salary_structure']);
        return new StaffPromotion([
            'payroll_number'=>$row['payroll_number'],
            'salary_structure'=>$ss->id,
            'level'=>$row['grade_level'],
            'step'=>$row['step'],
            'type'=>StaffPromotion::detectType($row['payroll_number'], $ss->id),
        ]);
    }

    // Exact (case-insensitive) match first, then a single structure whose name starts with the given one
    private static function findStructure($name)
    {
        $name = strtoupper(trim((string) $name));
        if ($name === '') {
            return null;
        }
        $ss = SalaryStructure::whereRaw('UPPER(TRIM(name)) = ?', [$name])->first();
        if (!$ss) {
            $matches = SalaryStructure::whereRaw('UPPER(TRIM(name)) LIKE ?', [$name . ' %'])->get();
            $ss = $matches->count() === 1 ? $matches->first() : null;
        }
        return $ss;
    }

    // Excel cells often carry stray spaces (e.g. "CONTISS "), clean them before validating
    public function prepareForValidation($data, $index)
    {
        $data['payroll_number'] = trim((string) ($data['payroll_number'] ?? ''));
        $data['salary_structure'] = trim((string) ($data['salary_structure'] ?? ''));
        // Use the exact structure name so the exists rule matches (e.g. "CONTISS" -> "CONTISS II")
        $data['salary_structure'] = self::findStructure($data['salary_structure'])->name ?? $data['salary_structure'];
        $data['grade_level'] = trim((string) ($data['grade_level'] ?? ''));
        $data['step'] = trim((string) ($data['step'] ?? ''));

        return $data;
    }




    public function batchSize(): int
    {
        return 100;
    }
    public function uniqueBy()
    {
//        return 'staff_number';
    }
    public function rules(): array
    {
        return [
            'payroll_number' => 'required|unique:staff_promotions,payroll_number|exists:employee_profiles,payroll_number',
            'salary_structure'=>'required|exists:salary_structures,name',
            'grade_level'=>'required',
            'step'=>'required|integer|min:1',
//            'payroll_number' => ['required',unique],
            // Above is alias for as it always validates in batches
//            '*.1' => Rule::in(['patrick@maatwebsite.nl']),

            // Can also use callback validation rules
//            '0' => function($attribute, $value, $onFailure) {
//                if ($value !== 'Patrick Brouwers') {
//                    $onFailure('Name is not Patrick Brouwers');
//                }
//            }
        ];
    }
    public function customValidationMessages()
    {
        return [
            'payroll_number.exists' => 'This payroll number does not exists in employees profile.',
            'payroll_number.unique' => 'Staff with the same payroll number exists.',
            'salary_structure.exists'=>'this salary structure does not exists',
            'grade_level.required'=>'Grade level is required',
            'step.required'=>'Step is required',
        ];
    }
}
