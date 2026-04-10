<div class="container">
    @php
        $hasEmployerPensionData = is_array($employer_pension)
            ? count($employer_pension) > 0
            : (isset($employer_pension) && $employer_pension->count() > 0);
        $reportContext = method_exists($this, 'employerPensionContextLabel')
            ? $this->employerPensionContextLabel()
            : 'All PFAs';
    @endphp
    <div class="row">

        <p></p>


        <div class="col-12 mt-5">

            <div class="panel panel-default panel-table">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col col-xs-6">
                            <h3 class="panel-title">
                                Employer Pension
                                @can('can_export')
                                    <button class="btn btn-dark float-right" wire:click.prevent="employer_pension_export()">Export</button>
                                @endcan
                            </h3>
                        </div>

                    </div>
                </div>
                @if($hasEmployerPensionData)
                    <div class="panel-body table-responsive">
                        <h5 style="text-align: center;padding:15px 0 5px;margin: 0">Employer Pension Report</h5>
                        <p style="text-align: center;margin: 0 0 10px 0">{{$reportContext}}</p>
                        <h6 style="text-align: center;padding:0 0 15px;margin: 0">For the month of {{\Illuminate\Support\Carbon::parse($date_from)->format('F Y')}} </h6>
                        <table style="width: 100%;border-collapse: collapse;font-size: 12px;" border="1" class="table table-striped table-bordered table-list table-sm">
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
                            $total=0
                            @endphp
                            <tbody>
                            @forelse($employer_pension as $report)

                                <tr>
                                    <td>{{$counter}}</td>
                                    <td>{{$report->ip_number}}</td>
                                    <td>{{$report->full_name}}</td>
                                    <td>{{$report->pension_pin}}</td>
                                    <td>

                                        {{number_format($report->employer_pension,2)}}
                                    </td>
                                </tr>
                                @php $counter++ @endphp
                            @empty

                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3"></td>
                                <th style="text-align: right">Total</th>
                                <th>{{number_format($employer_pension->sum('employer_pension'),2)}}</th>
                            </tr>
                            </tfoot>

                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
