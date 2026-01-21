<table>
   <tr> <td style="padding: 0;margin: 0;text-align: center;text-transform: uppercase">{{app_settings()->name}}</td></tr>
    <tr><td style="padding: 0;margin: 0;text-align: center">{{address()}}</td></tr>
   <tr> <td style="margin-top: 40px;margin-bottom: 0;padding: 0;text-align: center">The  Manager</td></tr>
   <tr> <td style="padding: 0;margin-top: 0;margin-bottom: 30px;text-align: center">SIGMA Pension Ltd </td></tr>
   <tr> <td style="padding: 0;margin: 0;text-align: center">Pension Deduction Schedule for the month of {{\Illuminate\Support\Carbon::parse($date)->format('F Y')}} </td></tr>
</table>
<table style="width: 100%;border-collapse: collapse;font-size: 12px;" border="1">
    <thead>
    <tr>
        <th>SN</th>
        <th>Payroll No. </th>
        <th>Staff Name </th>
        <th>Pension Pin</th>
        <th>Amount</th>
    </tr>
    </thead>
    @php
        $counter=1;
    @endphp
    <tbody>
    @forelse($reports as $report)
        @if($report->D3 != 0 && $report->D3 != '00')
        @php
            $deductions=\App\Models\Deduction::where('status','1')->get();
        $total=0

        @endphp
        <tr>
            <td>{{$counter}}</td>
            <td>{{$report->ip_number}}</td>
            <td>{{$report->full_name}}</td>
            <td>{{$report->pension_pin}}</td>
            <td>
                {{-- PFA Payment Schedule should only show employer pension contribution (D3) --}}
                @if($report->D3 != 0 && $report->D3 != '00')
                {{number_format($report->D3, 2)}}
                @endif
            </td>
        </tr>
        @php $counter++ @endphp
        @endif
    @empty

    @endforelse

    </tbody>

</table>
