{{--<h6 class="text-center text-dark">KEEP RECORD OF ANNUAL INCREMENT HISTORY</h6>--}}
<style>
    sup{
        color:orangered;
    }
</style>

<?php
$allowance = App\Models\Allowance::all();

?>
@can('can_mail')
    <button class="btn export float-right" wire:click.prevent="sendMail()">Send Mail <i class="fa fa-envelope"></i></button>
@endcan

@forelse($payslips as $paySlip)
    <?php
    $a = explode(" ", $paySlip->deduction_countdown);
    $sorts = collect($a)->sort();
    $loan = array();
    $search = "(";
    foreach ($sorts as $sort) {
        $loan[\Illuminate\Support\Str::before($sort, $search)] = "(" . \Illuminate\Support\Str::after($sort, '(');
    }
    ?>
    <table style="font-weight:bolder;margin:30px 9px 9px 9px ;width: 100%">
        <thead>
        <tr>
            <td style="max-width: 60px !important;"><img src="{{public_path('storage/' . app_settings()->logo)}}" alt="" style="width: 70px;position: relative;"></td>
            <td style="text-align: left;padding: 0 !important;">

                <p style="padding: 0 !important;text-align: center;margin: 0">{{app_settings()->name}}</p>
                <p style="padding: 0 !important;text-align: center;margin: 0">{{address()}}</p>
                <p style="padding: 0 !important;text-align: center;">Employee Pay Slip for the Month of {{$paySlip->salary_month}}, {{$paySlip->salary_year}}</p>
            </td>
            <td style="width: 60px !important;">&nbsp;</td>
        </tr>
        {{--           <tr>--}}
        {{--               <td rowspan="3" style="max-width: 60px !important;"> <img src="{{public_path('storage/'.app_settings()->logo)}}" alt="" style="width: 70px;position: relative;"></td>--}}
        {{--               <td style="font-weight: bolder;padding: 0!important;"> <p>{{app_settings()->name}}</p></td>--}}
        {{--           </tr>--}}
        {{--           <tr>--}}
        {{--               <td style="padding-left: 5% !important;padding-top: 0 !important;padding-bottom: 0 !important;"> <h4 style="padding: 0;margin: 0;">{{address()}}</h4></td>--}}
        {{--           </tr>--}}
        {{--           <tr>--}}
        {{--               <td style="padding: 10px 0 0 5%" colspan="2"> <h5 style="padding: 0;margin: 0; ">Employee Pay Slip for the Month of {{$paySlip->salary_month}}, {{$paySlip->salary_year}}</h5></td>--}}
        {{--           </tr>--}}
        </thead>

    </table>
    <table style="margin: auto;width: 95%">



    <table   style="width: 75%;margin-left: 3%;font-size: 13px !important;" >

        @php
            $step = \App\Models\EmployeeProfile::where('staff_number', $paySlip->pf_number)->first()->step;
        @endphp

        <tbody>
        <tr>
            <td  style="margin-top: 40px;">Basic Sal: <br> Sal Arrears: </td>
            <td style="text-align: right"> {{round($paySlip->basic_salary, 2)}} <br>{{number_format($paySlip->salary_areas, 2)}}</td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

            <td colspan="2"><b>Deductions</b></td>
        </tr>
            @php
                $active_allowances = \App\Models\Allowance::where('status', 1)->get();
                $active_deductions = \App\Models\Deduction::where('status', 1)->get();
                $max_count = max($active_allowances->count(), $active_deductions->count());
            @endphp

            @for($i = 0; $i < $max_count; $i++)
                <tr>
                    @if(isset($active_allowances[$i]))
                        <td>{{ $active_allowances[$i]->allowance_name }}: </td>
                        <td style="text-align: right">{{ number_format($paySlip->{'A' . $active_allowances[$i]->id}, 2) }}</td>
                    @else
                        <td></td><td></td>
                    @endif

                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

                    @if(isset($active_deductions[$i]))
                        <td>{{ $active_deductions[$i]->deduction_name }}: </td>
                        <td style="text-align: right">
                            {{ number_format($paySlip->{'D' . $active_deductions[$i]->id}, 2) }} 
                            <sup>@if(array_key_exists('D' . $active_deductions[$i]->id, $loan)){{ $loan['D' . $active_deductions[$i]->id] }}@endif</sup>
                        </td>
                    @else
                        <td></td><td></td>
                    @endif
                </tr>
            @endfor

        <tr>
            <td style="font-weight: bolder" colspan="4"><b>Gross Pay:{{number_format($paySlip->gross_pay, 2)}}</b></td>
        </tr>
        <tr>
            <td style="font-weight: bolder" colspan="4"><b>Total Ded:{{number_format($paySlip->total_deduction, 2)}}</b></td>
        </tr>
        <tr>
            <td style="font-weight: bolder" colspan="4"><b>Net Pay: {{number_format($paySlip->net_pay, 2)}}</b></td>

        </tr>
        </tbody>
    </table>
@empty
    No record found
@endforelse

