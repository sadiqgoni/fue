<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        * {
            font-size: 11px;
            font-family: "Times New Roman";
        }

        th,
        td {
            margin-top: 0;
            padding: 2px;
            /*border: 1px solid black;*/

        }

        div.page_break {
            page-break-before: always;
        }

        #footer {
            position: fixed;
            right: 0px;
            bottom: 10px;
            text-align: center;
            border-top: 1px solid black;
        }

        #footer .page:after {
            content: counter(page, decimal);
        }

        @page {
            margin: 20px 30px 40px 50px;
        }

        sup {
            color: orangered;
        }
    </style>
    <title>Pay Slip</title>

</head>

<body>
    <div id="footer">
        <p class="page">Page </p>
    </div>
    <?php
$allowance = App\Models\Allowance::all();

?>

    @forelse($payslips as $paySlip)

        <table style="width: 100%;text-align: center">
            <tr>
                <td> <img src="{{public_path('storage/' . app_settings()->logo)}}" alt=""
                        style="width: 35px;position: relative;left: 130px"></td>
                <td colspan="2">
                    <h3 style="padding: 0;margin: 0;text-align: center;text-transform: uppercase">{{app_settings()->name}}
                    </h3>
                    <h4 style="padding: 0;margin: 0;text-align: center">{{address()}}</h4>
                    <h5 style="padding: 0;margin: 10px auto; text-align: center;font-size: 24px">Pay Slip Report For the
                        Month of {{$paySlip->salary_month}}, {{$paySlip->salary_year}}</h5>

                </td>
            </tr>
        </table>
        {{-- @if($loop->iteration %2 ==0)--}}
        {{-- <div style="margin-top: 30px"></div>--}}
        {{-- @endif--}}

        {{-- <h5 style="padding: 0;margin: 10px auto; text-align: center;font-size: 12px">Pay Slip Report For the Month of
            {{$paySlip->salary_month}}, {{$paySlip->salary_year}}</h5>--}}
        <?php
        $a = explode(" ", $paySlip->deduction_countdown);
        $sorts = collect($a)->sort();
        //        $str = preg_replace('/[^0-9.]+/', '', $str);
        $loan = array();
        $search = "(";
        foreach ($sorts as $sort) {
            $loan[\Illuminate\Support\Str::before($sort, $search)] = "(" . \Illuminate\Support\Str::after($sort, '(');
        }
        ?>
        <table style="margin:1px auto; width: 65%">
            <tr>
                <td><span>Name: {{$paySlip->full_name}}</span></td>
                <td><span>PFN: {{$paySlip->pf_number}}</span></td>
                <td> <span>IPP No: {{$paySlip->ip_number}}</span></td>
            </tr>
            <tr>
                @php
                    $step = \App\Models\EmployeeProfile::where('staff_number', $paySlip->pf_number)->first()->step;
                @endphp
                <td><span>PFA: {{pfa_name($paySlip->pfa_name)}}</span></td>
                <td><span>Pension PIN: {{$paySlip->pension_pin}}</span></td>
                <td><span>{{$paySlip->salary_structure}} {{$paySlip->grade_level}}/{{$step}}</span></td>
            </tr>
            <tr>
                <td><span>Department:{{$paySlip->department}}</span></td>
                <td><span>Bank Code: {{$paySlip->bank_code}}</span></td>
                <td><span>Acct No: {{$paySlip->account_number}}</span></td>
            </tr>

            @php
                $active_allowances = \App\Models\Allowance::where('status', 1)->get();
                $active_deductions = \App\Models\Deduction::where('status', 1)->get();

                // Prepare Column 1: Basic Sal, Arrears, then Allowances
                $col1_items = [];
                $col1_items[] = ['label' => 'Basic Sal', 'value' => round($paySlip->basic_salary, 2)];
                $col1_items[] = ['label' => 'Sal Arrears', 'value' => number_format($paySlip->salary_areas, 2)];
                foreach ($active_allowances as $allow) {
                    $col1_items[] = ['label' => $allow->allowance_name, 'value' => number_format($paySlip->{'A' . $allow->id}, 2)];
                }

                // Prepare Columns 2 & 3: Deductions distributed evenly
                $col2_items = [];
                $col3_items = [];

                // Reset keys to iterate cleanly
                $deductions_list = $active_deductions->values();

                foreach ($deductions_list as $k => $ded) {
                    $val = number_format($paySlip->{'D' . $ded->id}, 2);
                    $sup = array_key_exists('D' . $ded->id, $loan) ? $loan['D' . $ded->id] : '';
                    $item = ['label' => $ded->deduction_name, 'value' => $val, 'sup' => $sup];

                    // Even indices go to Col 2, Odd to Col 3
                    // Note: Logic in previous thought was k%2==0 -> Col 2.
                    // So Ded 0 -> Col 2, Ded 1 -> Col 3.
                    if ($k % 2 == 0)
                        $col2_items[] = $item;
                    else
                        $col3_items[] = $item;
                }

                $max_rows = max(count($col1_items), count($col2_items), count($col3_items));
            @endphp

            @for($i = 0; $i < $max_rows; $i++)
                <tr>
                    <td>
                        @if(isset($col1_items[$i]))
                            {{ $col1_items[$i]['label'] }}: {{ $col1_items[$i]['value'] }}
                        @endif
                    </td>
                    <td>
                        @if(isset($col2_items[$i]))
                            {{ $col2_items[$i]['label'] }}: {{ $col2_items[$i]['value'] }}
                            @if($col2_items[$i]['sup']) <sup>{{ $col2_items[$i]['sup'] }}</sup> @endif
                        @endif
                    </td>
                    <td>
                        @if(isset($col3_items[$i]))
                            {{ $col3_items[$i]['label'] }}: {{ $col3_items[$i]['value'] }}
                            @if($col3_items[$i]['sup']) <sup>{{ $col3_items[$i]['sup'] }}</sup> @endif
                        @endif
                    </td>
                </tr>
            @endfor

            <tr>
                <td style="font-weight: bolder">Gross Pay:{{number_format($paySlip->gross_pay, 2)}}</td>
                <td style="font-weight: bolder">Total Deduction:{{number_format($paySlip->total_deduction, 2)}}</td>
                <td style="font-weight: bolder">Net Pay: {{number_format($paySlip->net_pay, 2)}}</td>
            </tr>
        </table>




        {{-- @if($loop->iteration %2 ==0)--}}
        {{-- <div class="page_break"></div>--}}
        {{-- @endif--}}
    @empty
        No record found
    @endforelse





</body>

</html>