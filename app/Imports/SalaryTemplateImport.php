<?php

namespace App\Imports;

use App\Models\SalaryStructureTemplate;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class SalaryTemplateImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts, WithValidation
{
    use Importable;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        
        return new SalaryStructureTemplate([

            'id'=>$row['id'],
            'salary_structure_id'=>$row['salary_structure_id'],
            'grade_level'=>$row['grade_level'],
            'no_of_grade_steps'=>$row['no_of_grade_steps'],
            'Step1'=>$row['Step1'],
            'Step2'=>$row['Step2'],
            'Step3'=>$row['Step3'],
            'Step4'=>$row['Step4'],
            'Step5'=>$row['Step5'],
            'Step6'=>$row['Step6'],
            'Step7'=>$row['Step7'],
            'Step8'=>$row['Step8'],
            'Step9'=>$row['Step9'],
            'Step10'=>$row['Step10'],
            'Step11'=>$row['Step11'],
            'Step12'=>$row['Step12'],
            'Step13'=>$row['Step13'],
            'Step14'=>$row['Step14'],
            'Step15'=>$row['Step15'],
            'Step16'=>$row['Step16'],
            'Step17'=>$row['Step17'],
            'Step18'=>$row['Step18'],
            'Step19'=>$row['Step19'],
            'Step20'=>$row['Step20'],
            'created_at'=>$row['created_at'],
            'updated_at'=>$row['updated_at'],
        ]);
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
            '1' => Rule::in(['patrick@maatwebsite.nl']),

            // Above is alias for as it always validates in batches
            '*.1' => Rule::in(['patrick@maatwebsite.nl']),

            // Can also use callback validation rules
            '0' => function($attribute, $value, $onFailure) {
                if ($value !== 'Patrick Brouwers') {
                    $onFailure('Name is not Patrick Brouwers');
                }
            }
        ];
    }
}
