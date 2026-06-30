<?php

namespace App\Exports;

use App\Models\Bank;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\LocalGovt;
use App\Models\MaritalStatus;
use App\Models\PFA;
use App\Models\Rank;
use App\Models\Relationship;
use App\Models\Religion;
use App\Models\SalaryUpdate;
use App\Models\SalaryStructure;
use App\Models\StaffCategory;
use App\Models\State;
use App\Models\Tribe;
use App\Models\Union;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NewSystemEmployeeImportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\SalaryUpdate>
     */
    protected Collection $salaryUpdates;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\SalaryStructure>
     */
    protected Collection $salaryStructures;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Department>
     */
    protected Collection $departments;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Unit>
     */
    protected Collection $units;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Rank>
     */
    protected Collection $ranks;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\EmploymentType>
     */
    protected Collection $employmentTypes;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\StaffCategory>
     */
    protected Collection $staffCategories;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Bank>
     */
    protected Collection $banks;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\PFA>
     */
    protected Collection $pfas;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Union>
     */
    protected Collection $unions;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\State>
     */
    protected Collection $states;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\LocalGovt>
     */
    protected Collection $localGovts;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Relationship>
     */
    protected Collection $relationships;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Gender>
     */
    protected Collection $genders;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\MaritalStatus>
     */
    protected Collection $maritalStatuses;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Religion>
     */
    protected Collection $religions;

    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Tribe>
     */
    protected Collection $tribes;

    public function __construct()
    {
        $this->salaryUpdates = SalaryUpdate::query()
            ->get()
            ->keyBy('employee_id');
        $this->salaryStructures = SalaryStructure::query()
            ->get()
            ->keyBy('id');
        $this->departments = Department::query()->get()->keyBy('id');
        $this->units = Unit::query()->get()->keyBy('id');
        $this->ranks = Rank::query()->get()->keyBy('id');
        $this->employmentTypes = EmploymentType::query()->get()->keyBy('id');
        $this->staffCategories = StaffCategory::query()->get()->keyBy('id');
        $this->banks = Bank::query()->get()->keyBy('id');
        $this->pfas = PFA::query()->get()->keyBy('id');
        $this->unions = Union::query()->get()->keyBy('id');
        $this->states = State::query()->get()->keyBy('id');
        $this->localGovts = LocalGovt::query()->get()->keyBy('id');
        $this->relationships = Relationship::query()->get()->keyBy('id');
        $this->genders = Gender::query()->get()->keyBy('id');
        $this->maritalStatuses = MaritalStatus::query()->get()->keyBy('id');
        $this->religions = Religion::query()->get()->keyBy('id');
        $this->tribes = Tribe::query()->get()->keyBy('id');
    }

    public function collection(): Collection
    {
        return EmployeeProfile::query()
            ->orderBy('full_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'employment_id',
            'full_name',
            'department',
            'staff_category',
            'employment_type',
            'staff_number',
            'payroll_number',
            'status',
            'salary_structure',
            'date_of_first_appointment',
            'date_of_last_appointment',
            'date_of_retirement',
            'contract_termination_date',
            'post_held',
            'grade_level',
            'step',
            'rank',
            'unit',
            'phone_number',
            'email',
            'bank_name',
            'account_number',
            'bank_code',
            'pfa_name',
            'pension_pin',
            'gender',
            'religion',
            'tribe',
            'marital_status',
            'nationality',
            'state_of_origin',
            'local_government',
            'tax_id',
            'bvn',
            'staff_union',
            'name_of_next_of_kin',
            'next_of_kin_phone_number',
            'relationship',
            'address',
            'employee_id',
            'basic_salary',
            ...$this->columns('A', 1, 50),
            ...$this->columns('D', 1, 50),
            'salary_arears',
            'gross_pay',
            'total_allowance',
            'total_deduction',
            'net_pay',
            'deduction_countdown',
            'nhis',
            'employer_pension',
        ];
    }

    public function map($employee): array
    {
        $salary = $this->salaryUpdates->get($employee->id);

        $row = [
            $employee->id,
            $employee->employment_id,
            $employee->full_name,
            $this->departmentName($employee->department),
            $this->staffCategoryName($employee->staff_category),
            $this->employmentTypeName($employee->employment_type),
            $employee->staff_number,
            $employee->payroll_number,
            $employee->status,
            $this->salaryStructureName($employee->salary_structure),
            $employee->date_of_first_appointment,
            $employee->date_of_last_appointment,
            $employee->date_of_retirement,
            $employee->contract_termination_date,
            $employee->post_held,
            $employee->grade_level,
            $employee->step,
            $this->rankName($employee->rank),
            $this->unitName($employee->unit),
            $employee->phone_number,
            $employee->email,
            $this->bankName($employee->bank_name),
            $employee->account_number,
            $employee->bank_code,
            $this->pfaName($employee->pfa_name),
            $employee->pension_pin,
            $this->genderName($employee->gender),
            $this->religionName($employee->religion),
            $this->tribeName($employee->tribe),
            $this->maritalStatusName($employee->marital_status),
            $employee->nationality,
            $this->stateName($employee->state_of_origin),
            $this->localGovtName($employee->local_government),
            $employee->tax_id,
            $employee->bvn,
            $this->unionName($employee->staff_union),
            $employee->name_of_next_of_kin,
            $employee->next_of_kin_phone_number,
            $this->relationshipName($employee->relationship),
            $employee->address,
            $employee->id,
            $this->value($salary, 'basic_salary'),
        ];

        foreach ($this->columns('A', 1, 50) as $column) {
            $row[] = $this->value($salary, $column);
        }

        foreach ($this->columns('D', 1, 50) as $column) {
            $row[] = $this->value($salary, $column);
        }

        $row[] = $this->value($salary, 'salary_arears', $this->value($salary, 'salary_areas'));
        $row[] = $this->value($salary, 'gross_pay');
        $row[] = $this->value($salary, 'total_allowance');
        $row[] = $this->value($salary, 'total_deduction');
        $row[] = $this->value($salary, 'net_pay');
        $row[] = $this->value($salary, 'deduction_countdown');
        $row[] = $this->value($salary, 'nhis');
        $row[] = $this->value($salary, 'employer_pension');

        return $row;
    }

    protected function value($record, string $key, $fallback = 0)
    {
        if (! $record) {
            return $fallback;
        }

        $value = data_get($record, $key);

        return $value === null ? $fallback : $value;
    }

    protected function columns(string $prefix, int $from, int $to): array
    {
        $columns = [];

        for ($index = $from; $index <= $to; $index++) {
            $columns[] = $prefix.$index;
        }

        return $columns;
    }

    protected function salaryStructureName($value): string
    {
        if (is_numeric($value)) {
            $structure = $this->salaryStructures->get((int) $value);

            return $structure?->name ?: (string) $value;
        }

        return (string) $value;
    }

    protected function departmentName($value): string
    {
        return $this->lookupDisplayValue($value, $this->departments, ['name']);
    }

    protected function unitName($value): string
    {
        return $this->lookupDisplayValue($value, $this->units, ['name']);
    }

    protected function rankName($value): string
    {
        return $this->lookupDisplayValue($value, $this->ranks, ['name']);
    }

    protected function employmentTypeName($value): string
    {
        return $this->lookupDisplayValue($value, $this->employmentTypes, ['name']);
    }

    protected function staffCategoryName($value): string
    {
        return $this->lookupDisplayValue($value, $this->staffCategories, ['name']);
    }

    protected function bankName($value): string
    {
        return $this->lookupDisplayValue($value, $this->banks, ['bank_name', 'name']);
    }

    protected function pfaName($value): string
    {
        return $this->lookupDisplayValue($value, $this->pfas, ['name']);
    }

    protected function unionName($value): string
    {
        return $this->lookupDisplayValue($value, $this->unions, ['name']);
    }

    protected function stateName($value): string
    {
        return $this->lookupDisplayValue($value, $this->states, ['name']);
    }

    protected function localGovtName($value): string
    {
        return $this->lookupDisplayValue($value, $this->localGovts, ['name']);
    }

    protected function relationshipName($value): string
    {
        return $this->lookupDisplayValue($value, $this->relationships, ['name']);
    }

    protected function genderName($value): string
    {
        return $this->lookupDisplayValue($value, $this->genders, ['name']);
    }

    protected function maritalStatusName($value): string
    {
        return $this->lookupDisplayValue($value, $this->maritalStatuses, ['name']);
    }

    protected function religionName($value): string
    {
        return $this->lookupDisplayValue($value, $this->religions, ['name']);
    }

    protected function tribeName($value): string
    {
        return $this->lookupDisplayValue($value, $this->tribes, ['name']);
    }

    protected function lookupDisplayValue($value, Collection $records, array $fields): string
    {
        if (! is_numeric($value)) {
            return (string) $value;
        }

        $record = $records->get((int) $value);

        if (! $record) {
            return (string) $value;
        }

        foreach ($fields as $field) {
            $resolved = data_get($record, $field);

            if ($resolved !== null && $resolved !== '') {
                return (string) $resolved;
            }
        }

        return (string) $value;
    }
}
