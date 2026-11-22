<?php

namespace App\Imports;

use App\Models\SalaryStructure;
use App\Models\SalaryStructureTemplate;
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
            'arrears_months'=>is_numeric($row['arrears_months'] ?? null) ? (int) $row['arrears_months'] : 0,
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




    // Reject rows whose grade level / step has no salary in the new structure's template,
    // so problems show at upload time instead of after posting.
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $index => $row) {
                if (!is_array($row) || $validator->errors()->has("$index.salary_structure") || $validator->errors()->has("$index.step") || $validator->errors()->has("$index.grade_level")) {
                    continue;
                }
                $ss = self::findStructure($row['salary_structure'] ?? '');
                $level = trim((string) ($row['grade_level'] ?? ''));
                $step = trim((string) ($row['step'] ?? ''));
                if (!$ss || $level === '' || $step === '') {
                    continue;
                }

                $template = SalaryStructureTemplate::where('salary_structure_id', $ss->id)
                    ->where('grade_level', is_numeric($level) ? (int) $level : $level)
                    ->first();
                if (!$template) {
                    $validator->errors()->add("$index.grade_level", "$ss->name grade level $level is not defined in the salary template");
                    continue;
                }
                $amount = ctype_digit($step) && (int) $step >= 1 && (int) $step <= 20 ? $template['Step' . (int) $step] : null;
                if (!$amount || (float) $amount <= 0) {
                    $validator->errors()->add("$index.step", "$ss->name grade level $level step $step has no salary in the salary template");
                }
            }
        });
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
            'arrears_months'=>'nullable|numeric|min:0',
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
