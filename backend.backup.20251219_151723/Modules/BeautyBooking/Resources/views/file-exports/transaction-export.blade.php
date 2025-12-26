
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Transaction_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
            <tr>
                <th>{{ translate('Filter_Criteria') }}</th>
                <th></th>
                <th>
                    {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                </th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('sl') }}</th>
                <th>{{ translate('Transaction_Type') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Amount') }}</th>
                <th>{{ translate('Booking_Reference') }}</th>
                <th>{{ translate('Date') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $transaction)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $transaction->transaction_type ?? 'unknown')) }}</td>
                    <td>{{ $transaction->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($transaction->amount ?? 0) }}</td>
                    <td>{{ $transaction->booking->booking_reference ?? translate('N/A') }}</td>
                    <td>{{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d H:i') : translate('N/A') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

