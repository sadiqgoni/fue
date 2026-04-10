<table>
    <tr>
        <th colspan="5" style="padding: 0;margin: 0;text-align: center;text-transform: uppercase">{{app_settings()->name}}</th>
    </tr>
    <tr>
        <th colspan="5" style="padding: 0;margin: 0;text-align: center">{{address()}}</th>
    </tr>
    <tr>
        <th colspan="5" style="padding: 12px 0 0;margin: 0;text-align: center">Employer Pension Report</th>
    </tr>
   
    <tr>
        <th colspan="5" style="padding: 0;margin: 0;text-align: center">{{$reportContext ?? 'All PFAs'}}</th>
    </tr>
    <tr>
        <th colspan="5" style="padding: 0 0 12px;margin: 0;text-align: center">For the month of {{\Illuminate\Support\Carbon::parse($date)->format('F Y')}}</th>
    </tr>
</table>

<table border="1" style="width: 100%;border-collapse: collapse;font-size: 12px;">
    <thead>
    <tr>
        <th>SN</th>
        <th>Payroll No.</th>
        <th>Staff Name</th>
        <th>Pension Pin</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    @php
        $counter = 1;
    @endphp
    @forelse($reports as $report)
        <tr>
            <td>{{$counter++}}</td>
            <td>{{$report->ip_number}}</td>
            <td>{{$report->full_name}}</td>
            <td>{{$report->pension_pin}}</td>
            <td>{{number_format($report->employer_pension, 2)}}</td>
        </tr>
    @empty
    @endforelse
    <tr>
        <td colspan="3"></td>
        <th>Total</th>
        <th>{{number_format($reports->sum('employer_pension'), 2)}}</th>
    </tr>
    </tbody>
</table>
