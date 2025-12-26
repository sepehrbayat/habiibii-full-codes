<!-- Transactions Tab -->
<div class="card">
    <div class="card-header py-2">
        <div class="search--button-wrapper">
            <h5 class="card-title">{{translate('messages.transactions')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$transactions->total()}}</span></h5>
        </div>
    </div>
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
                    <th class="border-0">{{translate('messages.date')}}</th>
                    <th class="border-0">{{translate('messages.type')}}</th>
                    <th class="border-0">{{translate('messages.amount')}}</th>
                    <th class="border-0">{{translate('messages.description')}}</th>
                    <th class="border-0">{{translate('messages.status')}}</th>
                </tr>
            </thead>
            <tbody id="set-rows">
                @foreach($transactions ?? [] as $key=>$transaction)
                    <tr>
                        <td>{{$key+(isset($transactions) && method_exists($transactions, 'firstItem') ? $transactions->firstItem() : 0)}}</td>
                        <td><span class="text--title">{{ $transaction->created_at->format('Y-m-d H:i') }}</span></td>
                        <td>
                            <span class="badge badge-soft-info">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</span>
                        </td>
                        <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($transaction->amount) }}</span></td>
                        <td><span class="text--title">{{ Str::limit($transaction->description ?? 'N/A', 50) }}</span></td>
                        <td>
                            <span class="badge badge-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(isset($transactions) && count($transactions) !== 0)
    <hr>
    @endif
    @if(isset($transactions))
        <div class="page-area">
            {!! $transactions->withQueryString()->links() !!}
        </div>
    @endif
    @if(isset($transactions) && count($transactions) === 0)
    <div class="empty--data">
        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
        <h5>
            {{translate('no_data_found')}}
        </h5>
    </div>
    @endif
</div>

