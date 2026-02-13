<?php

namespace App\Livewire\Forms;

use App\Models\ActivityLog;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Department;
use App\Models\EmploymentType;
use App\Models\SalaryAllowanceTemplate;
use App\Models\SalaryDeductionTemplate;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureTemplate;
use App\Models\SalaryUpdate;
use App\Models\StaffCategory;
use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class AnnualSalaryIncrement extends Component
{
    public $departments,
    $types,
    $categories,
    $salary_structures,
    $units;
    public $orderBy, $orderAsc = true;
    public $employee_type,
    $staff_category,
    $unit,
    $department,
    $salary_structure,
    $grade_level_from,
    $grade_level_to,
    $status;
    public $number_of_increment, $increment_date, $count;
    public $selection_mode = 'criteria'; // 'criteria' or 'specific'
    public $min_service_months;
    public $specific_employee_ids = [];
    use LivewireAlert;
    public $action_type = 'increment'; // 'increment' or 'revert'
    public $revert_preview = [];

    protected $rules = [
        'number_of_increment' => 'required_if:action_type,increment|integer|min:1|max:5',
        'increment_date' => 'required',
        'selection_mode' => 'required',
        'min_service_months' => 'nullable|integer|min:0',
        'specific_employee_ids' => 'required_if:selection_mode,specific|array',
        'arrears_months' => 'nullable|numeric|min:0',
    ];
    public $preview_employees = [];
    public $arrears_months;

    public function updated($pro)
    {
        $this->validateOnly($pro);

        if (in_array($pro, ['min_service_months', 'increment_date', 'salary_structure', 'grade_level_from', 'grade_level_to', 'employee_type', 'staff_category', 'status', 'unit', 'department'])) {
            $this->updatePreview();
        }
    }

    public function updatePreview()
    {
        if ($this->action_type == 'increment' && $this->selection_mode == 'criteria' && $this->min_service_months && $this->min_service_months > 0) {
            $employees = $this->getFilteredEmployees();
            $this->preview_employees = $employees->take(100);
            $this->revert_preview = [];
        } elseif ($this->action_type == 'revert' && $this->increment_date) {
            // Find increments to revert
            $this->preview_employees = [];
            $this->revert_preview = \App\Models\AnnualSalaryIncrement::whereDate('month_year', Carbon::parse($this->increment_date)->format('Y-m-d'))
                ->when($this->selection_mode == 'specific' && count($this->specific_employee_ids) > 0, function ($q) {
                    return $q->whereIn('employee_id', $this->specific_employee_ids);
                })
                // We could add more filters here if needed, but usually revert is purely time-based or specific person
                ->with('employee') // Assuming relationship exists, if not we might need to join
                ->get();
        } else {
            $this->preview_employees = [];
            $this->revert_preview = [];
        }
    }


    public function getFilteredEmployees()
    {
        $employees = collect();

        if ($this->selection_mode == 'criteria') {
            $employees = \App\Models\EmployeeProfile::when($this->salary_structure, function ($query) {
                return $query->where('salary_structure', $this->salary_structure);
            })
                ->when($this->grade_level_from, function ($query) {
                    return $query->whereBetween('grade_level', [$this->grade_level_from, $this->grade_level_to]);
                })
                ->when($this->employee_type, function ($query) {
                    return $query->where('employment_type', $this->employee_type);
                })
                ->when($this->staff_category, function ($query) {
                    return $query->where('staff_category', $this->staff_category);
                })
                ->when($this->status, function ($query) {
                    return $query->where('status', $this->status);
                })
                ->when($this->unit, function ($query) {
                    return $query->where('unit', $this->unit);
                })
                ->when($this->department, function ($query) {
                    return $query->where('department', $this->department);
                })
                ->get();
        } elseif ($this->selection_mode == 'specific') {
            $employees = \App\Models\EmployeeProfile::whereIn('id', $this->specific_employee_ids)->get();
        }

        // Filter by Tenure (Minimum Service Months)
        if ($this->selection_mode == 'criteria' && $this->min_service_months && $this->min_service_months > 0 && $this->increment_date) {
            $referenceDate = Carbon::parse($this->increment_date);
            $employees = $employees->filter(function ($employee) use ($referenceDate) {
                if (!$employee->date_of_first_appointment)
                    return false;
                $appointmentDate = Carbon::parse($employee->date_of_first_appointment);
                $employee->service_months_diff = $appointmentDate->diffInMonths($referenceDate); // Attach specific calc for display
                return $appointmentDate->lte($referenceDate) && $appointmentDate->diffInMonths($referenceDate) >= $this->min_service_months;
            });
        }

        return $employees;
    }

    protected $listeners = ['confirmed', 'canceled'];
    public function confirm()
    {
        $this->validate();

        if ($this->action_type == 'revert') {
            $count = count($this->revert_preview);
            if ($count == 0) {
                $this->alert('warning', 'No increments found to revert for this date/selection.');
                return;
            }

            $this->alert('question', "Are you sure you want to REVERT increments for $count employees? This will roll back their salary and step to the previous state.", [
                'showConfirmButton' => true,
                'showCancelButton' => true,
                'onConfirmed' => 'performRevert', // We'll trigger a separate method or handle in confirmed
                'onDismissed' => 'cancelled',
                'timer' => 90000,
                'position' => 'center',
                'confirmButtonText' => 'Yes, Revert',
            ]);
            return;
        }

        $exists = \App\Models\AnnualSalaryIncrement::where('increment_year', Carbon::parse($this->increment_date)->format('Y'))->get();
        // ... (existing increment confirm logic)
        $this->alert('question', ' This will increment salaries of the selected employees, do you want to continue?', [
            'showConfirmButton' => true,
            'showCancelButton' => true,
            'onConfirmed' => 'confirmed',
            'onDismissed' => 'cancelled',
            'timer' => 90000,
            'position' => 'center',
            'confirmButtonText' => 'Yes',
        ]);
    }

    // Add listener for performRevert
    public function getListeners()
    {
        return $this->listeners + ['performRevert' => 'performRevert'];
    }

    public function performRevert()
    {
        $this->authorize('can_save');
        set_time_limit(2000);

        $increments = $this->revert_preview; // cached from preview, or re-fetch to be safe
        // Re-fetch to be safe
        $increments = \App\Models\AnnualSalaryIncrement::whereDate('month_year', Carbon::parse($this->increment_date)->format('Y-m-d'))
            ->when($this->selection_mode == 'specific' && count($this->specific_employee_ids) > 0, function ($q) {
                return $q->whereIn('employee_id', $this->specific_employee_ids);
            })
            ->get();

        $count = 0;
        foreach ($increments as $inc) {
            $employee = \App\Models\EmployeeProfile::find($inc->employee_id);
            if (!$employee)
                continue;

            // Rollback Step
            $employee->step = $inc->old_grade_step;
            $employee->save();

            // Rollback Salary
            $salary_update = SalaryUpdate::where('employee_id', $employee->id)->first();
            if ($salary_update) {
                $basic_salary = $inc->current_salary; // The 'current_salary' in Increment table was the salary BEFORE increment
                $salary_update->basic_salary = $basic_salary;

                // Recalculate Allowances/Deductions for the OLD salary/step
                // We use the same logic as increment but with the restored values

                foreach (SalaryAllowanceTemplate::where('salary_structure_id', $employee->salary_structure)
                    ->whereRaw('? between grade_level_from and grade_level_to', [$employee->grade_level])
                    ->where('allowance_type', 1)->get() as $allowance) {
                    $salary_update["A$allowance->allowance_id"] = round($basic_salary / 100 * $allowance->value);
                }

                foreach (SalaryDeductionTemplate::where('salary_structure_id', $employee->salary_structure)
                    ->whereRaw('? between grade_level_from and grade_level_to', [$employee->grade_level])
                    ->where('deduction_type', 1)->get() as $deduction) {
                    $salary_update["D$deduction->deduction_id"] = round($basic_salary / 100 * $deduction->value);
                }

                $total_allowance = 0;
                $total_deduction = 0;
                foreach (Allowance::all() as $allow) {
                    $total_allowance += round($salary_update['A' . $allow->id] ?? 0, 2);
                }
                foreach (Deduction::all() as $ded) {
                    $total_deduction += round($salary_update['D' . $ded->id] ?? 0, 2);
                }

                $total_earning = round($basic_salary + $total_allowance, 2);
                $salary_update->gross_pay = $total_earning;
                $salary_update->net_pay = round($total_earning - $total_deduction, 2);
                $salary_update->total_deduction = $total_deduction;
                $salary_update->total_allowance = $total_allowance;
                $salary_update->save();
            }

            $inc->delete();
            $count++;
        }

        $this->alert('success', "Successfully reverted increments for $count employees.", ['timer' => 5000]);
        $this->revert_preview = []; // clear
        $this->updated('increment_date'); // refresh
    }

    public function confirmed()
    {
        $this->store();

    }
    public function store()
    {
        $this->authorize('can_save');
        $this->validate();

        set_time_limit(2000);
        $name = "Annual Salary Increment";
        backup_es($name);

        $employees = $this->getFilteredEmployees();

        $this->count = $employees->count();

        $actual_processed = 0;
        $skipped = 0;

        if ($employees->count() > 0) {
            foreach ($employees as $employee) {
                // Remove temporary display property to prevent saving error
                unset($employee->service_months_diff);

                $salary_structure = SalaryStructureTemplate::where('salary_structure_id', $employee->salary_structure)
                    ->where('grade_level', $employee->grade_level)
                    ->first();

                if (!$salary_structure) {
                    continue;
                }

                // Check if already incremented for this month
                $existingIncrement = \App\Models\AnnualSalaryIncrement::where('employee_id', $employee->id)
                    ->whereDate('month_year', Carbon::parse($this->increment_date)->format('Y-m-d'))
                    ->first();


                if ($existingIncrement) {
                    $skipped++;
                    continue;
                }

                if ($salary_structure != null) {
                    $grade_step = $employee->step;

                    // Logic to determine new step
                    if ($employee->step < $salary_structure->no_of_grade_steps) {
                        // Calculate potential new step
                        $potential_step = $employee->step + (int) $this->number_of_increment;

                        if ($potential_step <= $salary_structure->no_of_grade_steps) {
                            $grade_step = $potential_step;
                        } else {
                            $grade_step = $salary_structure->no_of_grade_steps;
                        }
                    } else {
                        // Already at max step, but we might still want to record the "attempt" or log it
                        // But per existing logic, if we enter here we might create a status=0 record?
                        // The original logic had an 'else' block for step check which saved status=0 logic
                        // We will preserve the logic: if step < max, we increment. If step >= max (else block), we save status=0
                    }

                    if ($employee->step < $salary_structure->no_of_grade_steps) {

                        // Recalculate grade_step logic to be safe
                        $potential_step = $employee->step + (int) $this->number_of_increment;
                        $grade_step = min($potential_step, $salary_structure->no_of_grade_steps);

                        $salary_update = SalaryUpdate::where('employee_id', $employee->id)->first();

                        // Robust check if salary update exists
                        if (!$salary_update) {
                            $salary_update = new SalaryUpdate();
                            $salary_update->employee_id = $employee->id;
                            // Assuming other fields might be needed or defaults, but let's proceed with existing pattern
                        }

                        $old_salary = $salary_update->basic_salary;
                        $old_gross_pay = $salary_update->gross_pay;
                        $old_grade_step = $employee->step;

                        // Safeguard access to array/property
                        $step_key = "Step" . $grade_step;
                        $annual_salary = $salary_structure->$step_key ?? 0;

                        $basic_salary = round($annual_salary / 12, 2);

                        $salary_update->basic_salary = $basic_salary;

                        // Update Allowances
                        foreach (SalaryAllowanceTemplate::where('salary_structure_id', $employee->salary_structure)
                            ->whereRaw('? between grade_level_from and grade_level_to', [$employee->grade_level])
                            ->where('allowance_type', 1)->get() as $allowance) {
                            $salary_update["A$allowance->allowance_id"] = round($basic_salary / 100 * $allowance->value);
                            $salary_update->save();
                        }

                        // Update Deductions
                        foreach (SalaryDeductionTemplate::where('salary_structure_id', $employee->salary_structure)
                            ->whereRaw('? between grade_level_from and grade_level_to', [$employee->grade_level])
                            ->where('deduction_type', 1)->get() as $deduction) {
                            $salary_update["D$deduction->deduction_id"] = round($basic_salary / 100 * $deduction->value);
                            $salary_update->save();
                        }

                        // Recalculate Totals
                        $total_allowance = 0;
                        $total_deduction = 0;
                        foreach (Allowance::all() as $allow) {
                            $total_allowance += round($salary_update['A' . $allow->id] ?? 0, 2);
                        }
                        foreach (Deduction::all() as $ded) {
                            $total_deduction += round($salary_update['D' . $ded->id] ?? 0, 2);
                        }

                        $total_earning = round($basic_salary + $total_allowance, 2);
                        $gross_pay = $total_earning;
                        $net_pay = round($gross_pay - $total_deduction, 2);

                        $salary_update->gross_pay = $gross_pay;
                        $salary_update->net_pay = $net_pay;
                        $salary_update->gross_pay = $gross_pay;
                        $salary_update->net_pay = $net_pay;

                        // Calculate Arrears
                        if ($this->arrears_months > 0) {
                            $increment_diff = $gross_pay - $old_gross_pay;
                            if ($increment_diff > 0) {
                                $arrears_val = round($increment_diff * $this->arrears_months, 2);
                                $salary_update->salary_arears = ($salary_update->salary_arears ?? 0) + $arrears_val;
                            }
                        }

                        $salary_update->save();

                        $employee->step = $grade_step;
                        $employee->save();

                        $incrementObj = new \App\Models\AnnualSalaryIncrement();
                        $incrementObj->employee_id = $employee->id;
                        $incrementObj->increment_month = Carbon::parse($this->increment_date)->format('F');
                        $incrementObj->increment_year = Carbon::parse($this->increment_date)->format('Y');
                        $incrementObj->month_year = Carbon::parse($this->increment_date)->format('Y-m-d');
                        $incrementObj->salary_structure = $employee->salary_structure;
                        $incrementObj->grade_level = $employee->grade_level;
                        $incrementObj->old_grade_step = $old_grade_step;
                        $incrementObj->new_grade_step = $grade_step;
                        $incrementObj->status = 1;
                        $incrementObj->current_salary = $old_salary;
                        $incrementObj->new_salary = $basic_salary;
                        $incrementObj->arrears_months = $this->arrears_months;
                        $incrementObj->save();

                        $actual_processed++;

                    } else {
                        // Already at Max Step, record status 0
                        $incrementObj = new \App\Models\AnnualSalaryIncrement();
                        $salary_update = SalaryUpdate::where('employee_id', $employee->id)->first();
                        $incrementObj->employee_id = $employee->id;
                        $incrementObj->increment_month = Carbon::parse($this->increment_date)->format('F');
                        $incrementObj->increment_year = Carbon::parse($this->increment_date)->format('Y');
                        $incrementObj->month_year = Carbon::parse($this->increment_date)->format('Y-m-d');
                        $incrementObj->salary_structure = $employee->salary_structure;
                        $incrementObj->grade_level = $employee->grade_level;
                        $incrementObj->old_grade_step = $employee->step;
                        $incrementObj->new_grade_step = $employee->step;
                        $incrementObj->status = 0;
                        $incrementObj->current_salary = $salary_update->basic_salary ?? 0;
                        $incrementObj->new_salary = $salary_update->basic_salary ?? 0;
                        $incrementObj->save();

                        // We count this as processed even if not incremented, as an action was taken
                        $actual_processed++;
                    }
                }
            }

            $msg = "Processed $actual_processed employees successfully.";
            if ($skipped > 0) {
                $msg .= " ($skipped skipped as already incremented for this month)";
            }

            $this->alert('success', $msg, [
                "timer" => 9000
            ]);

            // Clear selection on success
            $this->specific_employee_ids = [];

            $user = Auth::user();
            $log = new ActivityLog();
            $log->user_id = $user->id;
            $log->action = "Incremented $actual_processed employees salary";
            $log->save();

        } else {
            $this->alert('warning', no_record(), ['timer' => 9200]);
        }
    }
    public function mount()
    {
        $this->departments = [];
    }
    public function updatedUnit()
    {
        if ($this->unit != '') {
            $this->departments = Department::where('unit_id', $this->unit)->get();
        } else {
            $this->departments = [];
        }
    }
    public function render()
    {
        $this->types = EmploymentType::all();
        $this->categories = StaffCategory::all();
        $this->salary_structures = SalaryStructure::where('status', 1)->get();
        $this->units = Unit::where('status', 1)->get();
        $deductions = Deduction::all();

        $specific_candidates = [];
        if ($this->selection_mode == 'specific') {
            // Fetch employees grouped by Grade and Step
            $specific_candidates = \App\Models\EmployeeProfile::where('status', 1)
                ->select('id', 'full_name', 'staff_number', 'grade_level', 'step')
                ->orderBy('grade_level')
                ->orderBy('step')
                ->orderBy('full_name')
                ->get()
                ->groupBy(function ($item) {
                    return 'Grade Level ' . $item->grade_level . ' - Step ' . $item->step;
                });
        }

        return view('livewire.forms.annual-salary-increment', [
            'specific_candidates' => $specific_candidates
        ])->extends('components.layouts.app');
    }
}
