
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Subscription_List') }}</h1></div>
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
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Subscription_Type') }}</th>
                <th>{{ translate('Start_Date') }}</th>
                <th>{{ translate('End_Date') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $subscription)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $subscription->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $subscription->subscription_type ?? 'basic')) }}</td>
                    <td>{{ $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d') : translate('N/A') }}</td>
                    <td>{{ $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d') : translate('N/A') }}</td>
                    <td>{{ ucwords($subscription->status ?? 'pending') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

