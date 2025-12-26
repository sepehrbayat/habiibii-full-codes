
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Loyalty_Campaign_List') }}</h1></div>
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
                <th>{{ translate('Campaign_Name') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Points_Per_Booking') }}</th>
                <th>{{ translate('Start_Date') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $campaign)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $campaign->name }}</td>
                    <td>{{ $campaign->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ $campaign->points_per_booking ?? 0 }}</td>
                    <td>{{ $campaign->start_date ? \Carbon\Carbon::parse($campaign->start_date)->format('Y-m-d') : translate('N/A') }}</td>
                    <td>{{ $campaign->status == 'active' ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

