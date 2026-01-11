<?php

namespace App\Imports;

use App\Models\SalaryStructureTemplate;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalaryTemplate implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $data;
    public function __construct($data)
    {
        $this->data=$data;
    }
    public function model(array $row)
    {
        // dd($row['step2']);
        return new SalaryStructureTemplate([
            'salary_structure_id'=>$this->data,
            'grade_level'=>$row['grade_level_from'],
            // 'grade_level_to'=>$row['grade_level_to'],
            'no_of_grade_steps'=>$row['no_of_grade_steps'],
            'Step1'=>$row['step1'],
            'Step2'=>$row['step2'],
            'Step3'=>$row['step3'],
            'Step4'=>$row['step4'],
            'Step5'=>$row['step5'],
            'Step6'=>$row['step6'],
            'Step7'=>$row['step7'],
            'Step8'=>$row['step8'],
            'Step9'=>$row['step9'],
            'Step10'=>$row['step10'],
            'Step11'=>$row['step11'],
            'Step12'=>$row['step12'],
            'Step13'=>$row['step13'],
            'Step14'=>$row['step14'],
            'Step15'=>$row['step15'],
            'Step16'=>$row['step16'],
            'Step17'=>$row['step17'],
            'Step18'=>$row['step18'],
            'Step19'=>$row['step19'],
            'Step20'=>$row['step20'],
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
