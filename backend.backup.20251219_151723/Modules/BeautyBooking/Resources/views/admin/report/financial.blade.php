@extends('layouts.admin.app')

@section('title', translate('Financial Reports'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{asset('/public/assets/admin/img/report/report.png')}}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Financial_Reports') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $transactions->total() }}</span>
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form method="get">
                    <div class="row g-2 mb-20">
                        <div class="col-sm-6 col-md-3">
                            <div class="select-item">
                                <label for="date-from" class="input-label">{{ translate('messages.From_Date') }}</label>
                                <input type="date" id="date-from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="select-item">
                                <label for="date-to" class="input-label">{{ translate('messages.To_Date') }}</label>
                                <input type="date" id="date-to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="select-item">
                                <label for="transaction-type" class="input-label">{{ translate('messages.Type') }}</label>
                                <select id="transaction-type" name="transaction_type" class="js-data-example-ajax form-control set-filter opacity-70">
                                    <option value="">{{ translate('messages.All_Types') }}</option>
                                    <option value="commission" {{ request('transaction_type') == 'commission' ? 'selected' : '' }}>
                                        {{ translate('messages.Commission') }}
                                    </option>
                                    <option value="service_fee" {{ request('transaction_type') == 'service_fee' ? 'selected' : '' }}>
                                        {{ translate('messages.Service_Fee') }}
                                    </option>
                                    <option value="subscription" {{ request('transaction_type') == 'subscription' ? 'selected' : '' }}>
                                        {{ translate('messages.Subscription') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end mt-4">
                                <button type="reset" id="reset_btn"
                                        class="btn btn--reset min-w-120px">{{ translate('messages.reset') }}</button>
                                <button type="submit"
                                        class="btn btn--primary min-w-120px text-center">{{ translate('messages.filter') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Header -->
            <div class="card-header justify-content-between gap-3 py-2 flex-wrap">
                <h5 class="card-title">{{translate('messages.financial_report')}}</h5>
            </div>
            <!-- End Header -->
            <div class="card-body">
                <ul class="transaction--information text-uppercase mb-3">
                    <li class="text--info">
                        <i class="tio-document-text-outlined"></i>
                        <div>
                            <span>{{translate('messages.total_commission')}}</span> <strong>{{\App\CentralLogics\Helpers::format_currency($totalCommission)}}</strong>
                        </div>
                    </li>
                    <li class="seperator"></li>
                    <li class="text--success">
                        <i class="tio-checkmark-circle-outlined success--icon"></i>
                        <div>
                            <span>{{translate('messages.total_service_fee')}}</span> <strong>{{\App\CentralLogics\Helpers::format_currency($totalServiceFee)}}</strong>
                        </div>
                    </li>
                </ul>
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "paging":false
                            }'>
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('sl')}}</th>
                                <th class="border-0">{{translate('messages.id')}}</th>
                                <th class="border-0">{{translate('messages.type')}}</th>
                                <th class="border-0">{{translate('messages.salon')}}</th>
                                <th class="border-0">{{translate('messages.amount')}}</th>
                                <th class="border-0">{{translate('messages.commission')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0">{{translate('messages.date')}}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($transactions as $key=>$transaction)
                                <tr>
                                    <td>{{$key+$transactions->firstItem()}}</td>
                                    <td>{{ $transaction->id }}</td>
                                    <td><span class="text--title">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($transaction->salon->store->name ?? 'N/A', 20) }}</span></td>
                                    <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($transaction->amount) }}</span></td>
                                    <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($transaction->commission) }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td><span class="text--title">{{ $transaction->created_at->format('Y-m-d H:i') }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($transactions) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $transactions->withQueryString()->links() !!}
                </div>
                @if(count($transactions) === 0)
                <div class="empty--data">
                    <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

