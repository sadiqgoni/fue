<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EmployerPensionExport implements FromView
{
    public $reports;
    public $date;
    public $reportContext;
    public $reportScope;

    public function __construct($reports, $date, $reportContext = 'All PFAs', $reportScope = 'Group Report')
    {
        $this->reports = $reports;
        $this->date = $date;
        $this->reportContext = $reportContext;
        $this->reportScope = $reportScope;
    }

    public function view(): View
    {
        return view('exports.employer_pension_export', [
            'reports' => $this->reports,
            'date' => $this->date,
            'reportContext' => $this->reportContext,
            'reportScope' => $this->reportScope,
        ]);
    }
}
