<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        *{
            font-size: 11px;
            font-family: "Times New Roman";
        }
        th, td{
            margin-top: 0;
            padding: 1px 2px;
            /*border: 1px solid black;*/

        }
        div.page_break{
            page-break-before: always;
        }
        #footer { position: fixed; right: 0px; bottom: 10px; text-align: center;border-top: 1px solid black;}
        #footer .page:after { content: counter(page, decimal); }
        /*@page { margin: 20px 30px 40px 50px; }*/
        sup{
            color: orangered;
        }
        @media print {
            @page  {
                size:A4 portrait;
            }

        }
    </style>
    <title>Pay Slip</title>

</head>
<body onload="window.print()">
{{--<div id="footer">--}}
{{--    <p class="page">Page </p>--}}
{{--</div>--}}
<?php
$allowance=App\Models\Allowance::all();
?>
@forelse($paySlips as $pay)

    @forelse($pay as $paySlip)
        {{--        @dd($loop->iteration)--}}
        <?php
        //        $allowance=\App\Models\Allowance::find($paySlip);
        //        dd($allowance)

        $a=explode(" ",$paySlip->deduction_countdown);
        $sorts=collect($a)->sort();
        //        $str = preg_replace('/[^0-9.]+/', '', $str);
        $loan=array();
        $search="(";
        foreach ($sorts as $sort){
            $loan[\Illuminate\Support\Str::before($sort,$search)]="(".\Illuminate\Support\Str::after($sort,'(');
        }
        ?>
        {{--        <!--@if($loop->iteration %2 ==0)-->--}}
        <div style="margin: 10px 0"></div>
        {{--        <!--@endif-->--}}
        {{--<img src="{{public_path(logo())}}" alt="" style="width: 50px;position: relative;float:left;">--}}



        {{--        <br>--}}

        <table style="margin:9px auto;width: 70%" >
            <tr>
                <td rowspan="2"> <img src="{{asset('storage/'.logo())}}" alt="" style="width: 50px;position: relative;float:left;"></td>
                <td> <h3 style="padding: 0;margin: 5px;text-align: center;text-transform: uppercase;">{{app_settings()->name}}</h3></td>
            </tr>
            <tr>
                <td> <h4 style="padding: 0;margin: 0;text-align: center">{{address()}}</h4></td>
            </tr>
            <tr>
                <td></td>
                <td > <h5 style="padding: 0;margin: 0; text-align: center">STAFF PAY SLIP For the Month of {{$paySlip->salary_month}}, {{$paySlip->salary_year}}</h5></td>
            </tr>
        </table>
        <table   style="margin:1px auto" >
            <tr>
                <!--group-->
                <td><span>Name: {{$paySlip->full_name}}</span></td>
                <td><span>PFN: {{$paySlip->pf_number}}</span></td>
                <td> <span>IPP No: {{$paySlip->ip_number}}</span></td>
            </tr>
            <tr>
                @php
                    $step=\App\Models\EmployeeProfile::where('staff_number',$paySlip->pf_number)->first()->step;
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
            <tr>
                <td  style="margin-top: 40px;">Basic Sal: {{round($paySlip->basic_salary,2)}}</td>
                <td  style="margin-top: 40px;">COEASU LS: {{number_format($paySlip->D7,2)}} <sup>@if(array_key_exists('D7',$loan)){{$loan['D7']}}@endif</sup></td>
                <td  style="margin-top: 40px;">Cool Bucks: {{number_format($paySlip->D25,2)}} <sup>@if(array_key_exists('D25',$loan)){{$loan['D25']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Sal Arrears: {{number_format($paySlip->salary_areas,2)}}</td>
                <td>NASU UD: {{number_format($paySlip->D8,2)}} <sup>@if(array_key_exists('D8',$loan)){{$loan['D8']}}@endif</sup> </td>
                <td>Fast Cr: {{number_format($paySlip->D26,2)}} <sup>@if(array_key_exists('D26',$loan)){{$loan['D26']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Pecu Allow: {{number_format($paySlip->A1,2)}}</td>
                <td>NASU Ls: {{number_format($paySlip->D9,2)}} <sup>@if(array_key_exists('D9',$loan)){{$loan['D9']}}@endif</sup> </td>
                <td>Fast Cash: {{number_format($paySlip->D27,2)}} <sup>@if(array_key_exists('D27',$loan)){{$loan['D27']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Res Allow: {{number_format($paySlip->A2,2)}}</td>
                <td>SSUCOE UD: {{number_format($paySlip->D10,2)}} <sup>@if(array_key_exists('D10',$loan)){{$loan['D10']}}@endif</sup> </td>
                <td>GWCU CND: {{number_format($paySlip->D28,2)}} <sup>@if(array_key_exists('D28',$loan)){{$loan['D28']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Rent Allow: {{number_format($paySlip->A3,2)}}</td>
                <td>SSUCOE Ls: {{number_format($paySlip->D11,2)}} <sup>@if(array_key_exists('D11',$loan)){{$loan['D11']}}@endif</sup> </td>
                <td>GBPL Centre: {{number_format($paySlip->D29,2)}} <sup>@if(array_key_exists('D29',$loan)){{$loan['D29']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Shift Allow: {{number_format($paySlip->A4,2)}}</td>
                <td>H Loan : {{number_format($paySlip->D12,2)}} <sup>@if(array_key_exists('D12',$loan)){{$loan['D12']}}@endif</sup> </td>
                <td>LAPO MFB: {{number_format($paySlip->D30,2)}} <sup>@if(array_key_exists('D30',$loan)){{$loan['D30']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Call Duty Allow: {{number_format($paySlip->A5,2)}}</td>
                <td>ASPTFund: {{number_format($paySlip->D13,2)}} <sup>@if(array_key_exists('D13',$loan)){{$loan['D13']}}@endif</sup> </td>
                <td>Hastelloy DV: {{number_format($paySlip->D31,2)}} <sup>@if(array_key_exists('D31',$loan)){{$loan['D31']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Haz Allow: {{number_format($paySlip->A6,2)}}</td>
                <td>Rent GQ: {{number_format($paySlip->D14,2)}} <sup>@if(array_key_exists('D14',$loan)){{$loan['D14']}}@endif</sup> </td>
                <td>LSheGo MFB: {{number_format($paySlip->D32,2)}} <sup>@if(array_key_exists('D32',$loan)){{$loan['D32']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>New Haz Allow: {{number_format($paySlip->A7,2)}}</td>
                <td>Car Loan: {{number_format($paySlip->D15,2)}} <sup>@if(array_key_exists('D15',$loan)){{$loan['D15']}}@endif</sup> </td>
                <td>Page IF: {{number_format($paySlip->D33,2)}} <sup>@if(array_key_exists('D33',$loan)){{$loan['D33']}}@endif</sup> </td>

            </tr>
            <tr>
                <td>Other Alw1: {{number_format($paySlip->A8,2)}}</td>
                <td>FMB HRLR: {{number_format($paySlip->D16,2)}}  <sup>@if(array_key_exists('D16',$loan)){{$loan['D16']}}@endif</sup> </td>
                <td>Spec MFB: {{number_format($paySlip->D34,2)}}  <sup>@if(array_key_exists('D34',$loan)){{$loan['D34']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Other Alw2: {{number_format($paySlip->A9,2)}}</td>
                <td>NASU WFS: {{number_format($paySlip->D17,2)}} <sup>@if(array_key_exists('D17',$loan)){{$loan['D17']}}@endif</sup> </td>
                <td>UBA Consu {{number_format($paySlip->D35,2)}} <sup>@if(array_key_exists('D35',$loan)){{$loan['D35']}}@endif</sup> </td>
            </tr>

            <tr>
                <td style="font-weight: bold">Deductions</td>
                <td>VehRefurb: {{number_format($paySlip->D18,2)}} <sup>@if(array_key_exists('D18',$loan)){{$loan['D18']}}@endif</sup> </td>
                <td>UCEE MFB:{{number_format($paySlip->D36,2)}} <sup>@if(array_key_exists('D36',$loan)){{$loan['D36']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Income Tax : {{number_format($paySlip->D1,2)}} <sup>@if(array_key_exists('D1',$loan)){{$loan['D1']}}@endif</sup> </td>
                <td>Staff Coop: {{number_format($paySlip->D19,2)}} <sup>@if(array_key_exists('D19',$loan)){{$loan['D19']}}@endif</sup> </td>
                <td>Coop LR:{{number_format($paySlip->D37,2)}} <sup>@if(array_key_exists('D37',$loan)){{$loan['D37']}}@endif</sup> </td>

            </tr>
            <tr>
                <td>NHF: {{number_format($paySlip->D2,2)}} <sup>@if(array_key_exists('D2',$loan)){{$loan['D2']}}@endif</sup> </td>
                <td>FCE WICE: {{number_format($paySlip->D20,2)}} <sup>@if(array_key_exists('D20',$loan)){{$loan['D20']}}@endif</sup> </td>
                <td>OTHER DED: {{number_format($paySlip->D38,2)}} <sup>@if(array_key_exists('D38',$loan)){{$loan['D38']}}@endif</sup> </td>

            </tr>
            <tr>
                <td>CPS:{{number_format($paySlip->D3,2)}} <sup>@if(array_key_exists('D3',$loan)){{$loan['D3']}}@endif</sup> </td>
                <td>ECCE:{{number_format($paySlip->D21,2)}} <sup>@if(array_key_exists('D21',$loan)){{$loan['D21']}}@endif</sup> </td>
                <td>Tetfund schrfd: {{number_format($paySlip->D39,2)}} <sup>@if(array_key_exists('D39',$loan)){{$loan['D39']}}@endif</sup> </td>

            </tr>
            <tr>
                <td>FCE Res: {{number_format($paySlip->D4,2)}} <sup>@if(array_key_exists('D4',$loan)){{$loan['D4']}}@endif</sup> </td>
                <td>DPS: {{number_format($paySlip->D22,2)}} <sup>@if(array_key_exists('D22',$loan)){{$loan['D22']}}@endif</sup> </td>
                <td>OTHER DED2: {{number_format($paySlip->D40,2)}} <sup>@if(array_key_exists('D40',$loan)){{$loan['D40']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>Sal Ded: {{number_format($paySlip->D5,2)}} <sup>@if(array_key_exists('D22',$loan)){{$loan['D5']}}@endif</sup> </td>
                <td>SSS: {{number_format($paySlip->D23,2)}} <sup>@if(array_key_exists('D23',$loan)){{$loan['D23']}}@endif</sup> </td>
                <td>OTHER DED3: {{number_format($paySlip->D41,2)}} <sup>@if(array_key_exists('D41',$loan)){{$loan['D41']}}@endif</sup> </td>
            </tr>
            <tr>
                <td>COEASU UD: {{number_format($paySlip->D6,2)}} <sup>@if(array_key_exists('D6',$loan)){{$loan['D6']}}@endif</sup> </td>
                <td>City Gate: {{number_format($paySlip->D24,2)}} <sup>@if(array_key_exists('D24',$loan)){{$loan['D24']}}@endif</sup> </td>
                <td>OTHER DED4: {{number_format($paySlip->D42,2)}} <sup>@if(array_key_exists('D42',$loan)){{$loan['D42']}}@endif</sup> </td>
            </tr>
            <tr>
                <td style="font-weight: bolder">Gross Pay:{{number_format($paySlip->gross_pay,2)}}</td>
                <td style="font-weight: bolder">Total Deduction:{{number_format($paySlip->total_deduction,2)}}</td>
                <td style="font-weight: bolder">Net Pay: {{number_format($paySlip->net_pay,2)}}</td>
            </tr>
        </table>




    @empty

    @endforelse
    <!--@if($loop->iteration %2 ==1)-->
    <!--    <div class="page_break"></div>-->
    <!--@endif-->

    @empty
        <tr style="color:red;">No Record</tr>
    @endforelse


</body>
</html>
