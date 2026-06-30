<?php

namespace App\Livewire\Forms;

use App\Models\ActivityLog;
use App\Models\Bank;
use App\Models\Deduction;
use App\Models\SalaryHistory;
use App\Models\TemporaryBankPaymentSummary;
use App\Models\TemporaryDeduction;
use App\Models\TemporatyBankPaymentReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class SalaryLedgerPosting extends Component
{
    public $month,$year,$description,$ids;
    use LivewireAlert;
    public function getListeners()
    {
        return['confirmed', 'dismissed'];
    }

    protected $rules=[
        'month'=>'required',
        'year'=>'required',
        'description'=>'required',
    ];
    public function mount()
    {
        $this->year = date('Y');
        $this->month = date('F');
        $this->generateDescription();
    }

    public function generateDescription()
    {
        if ($this->month || $this->year) {
            $institutionName = strtoupper(app_settings()->name ?? 'INSTITUTION');
            $month = $this->month ?: date('F');
            $year = $this->year ?: date('Y');
            $this->description = $institutionName . ' SALARY ' . strtoupper($month) . ' ' . $year;
        }
    }

    public function updatedMonth()
    {
        $this->generateDescription();
    }

    public function updatedYear()
    {
        $this->generateDescription();
    }
    public function store()
    {

        $this->validate();
        if (SalaryHistory::where('salary_month',$this->month)->where('salary_year',$this->year)->exists()){
            $this->alert('warning','“Record Exist for '.$this->month. ' '. $this->year.' Do you want to overwrite?',[
                'showConfirmButton' => true,
                'confirmButtonText' => 'Yes',
                'onConfirmed' => 'confirmed',
                'showCancelButton' => true,
                'onDismissed' => 'cancelled',
                'position' => 'center',
                'timer'=>90000,
//                'timerProgressBar'=>true,
                'toast' => true,
            ]);
        }else {
           $this->store_record();
        }
    }
    public function store_record()
    {
        set_time_limit(2000);

        $this->months=Carbon::parse($this->month)->format('F');
        $employees=\App\Models\EmployeeProfile::join('salary_updates','salary_updates.employee_id','employee_profiles.id')
            ->select([
                'salary_updates.*',
                'employee_profiles.full_name',
                'employee_profiles.staff_number',
                'employee_profiles.payroll_number',
                'employee_profiles.department',
                'employee_profiles.staff_category',
                'employee_profiles.employment_type',
                'employee_profiles.phone_number',
                'employee_profiles.status',
                'employee_profiles.bank_code',
                'employee_profiles.account_number',
                'employee_profiles.bank_name',
                'employee_profiles.pfa_name',
                'employee_profiles.pension_pin',
                'employee_profiles.grade_level',
                'employee_profiles.unit',
                'employee_profiles.salary_structure',
                'employee_profiles.step',

            ])
            ->where('employee_profiles.status',1)
//            ->limit(900)
            ->get();
        foreach ($employees as $employee) {
            $description=$this->description;
            $salary = new SalaryHistory();
            $salary->salary_month = $this->months;
            $salary->salary_year = $this->year;

            $salary->pf_number = $employee->staff_number;
            $salary->ip_number = $employee->payroll_number;
            $salary->full_name = $employee->full_name;
            $salary->department = dept($employee->department);
            $salary->staff_category = staff_cat($employee->staff_category);
            $salary->phone_number = $employee->phone_number;
            $salary->employment_type = emp_type($employee->employment_type);
            $salary->employment_status = emp_status($employee->status);
            $salary->salary_structure = ss($employee->salary_structure);
            $salary->grade_level = $employee->grade_level;
            $salary->step = $employee->step;
            $salary->unit = unit_name($employee->unit);
            $salary->bank_code = $employee->bank_code;
            $salary->account_number = $employee->account_number;
            $salary->bank_name = $employee->bank_name;
            $salary->pfa_name = $employee->pfa_name;
            $salary->pension_pin = $employee->pension_pin;
            $salary->basic_salary = round($employee->basic_salary,2);
            for ($index = 1; $index <= 50; $index++) {
                $allowanceColumn = 'A' . $index;
                $deductionColumn = 'D' . $index;

                $salary->$allowanceColumn = $employee->$allowanceColumn ?? 0;
                $salary->$deductionColumn = $employee->$deductionColumn ?? 0;
            }
            $salary->salary_areas = $employee->salary_arears;
            $salary->gross_pay = $employee->gross_pay;
            $salary->total_deduction = $employee->total_deduction;
            $salary->total_allowance = $employee->total_allowance;
            $salary->net_pay = $employee->net_pay;
            $salary->deduction_countdown = $employee->deduction_countdown;
            $salary->nhis = $employee->nhis;
            $salary->employer_pension = $employee->employer_pension;
            $salary->salary_remark = $description;

            $date_month=$this->month."-".$this->year;
            $salary->date_month = Carbon::parse($date_month)->format('Y-m-d');
            $salary->save();

            $this->alert('success','Record have been posted successfully');
            $user=Auth::user();
            $log=new ActivityLog();
            $log->user_id=$user->id;
            $log->action="Posted to ledger ";
            $log->save();

        }

    }
    public function update_record()
    {

        set_time_limit(2000);
        $salaries = SalaryHistory::where('salary_month',$this->month)->where('salary_year',$this->year)
            ->get();
        foreach ($salaries as $salary){
            try
            {
                $ids = explode(",", $salary->id);
                // call delete on the query builder (no get())
                SalaryHistory::destroy($ids);
            }catch (\Exception){}
        }
        $this->store_record();
        $this->alert('success','Salary ledger for the month of '.$this->month.' '.$this->year.' have been updated');
        $user=Auth::user();
        $log=new ActivityLog();
        $log->user_id=$user->id;
        $log->action="updated ledger record ";
        $log->save();
    }


    public function updated($pop){
        $this->validateOnly($pop);
    }
    public function confirmed()
    {
        $this->update_record();
    }
    public function render()
    {
        $recentSalaries = SalaryHistory::select('salary_month', 'salary_year', DB::raw('COUNT(*) as staff_count'))
            ->groupBy('salary_month', 'salary_year')
            ->orderBy('salary_year', 'desc')
            ->orderBy('salary_month', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.forms.salary-ledger-posting', compact('recentSalaries'))->extends('components.layouts.app');
    }
}
