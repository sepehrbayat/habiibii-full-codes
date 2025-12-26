
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Salon_List') }}</h1></div>
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
                <th>{{ translate('Salon_Name') }}</th>
                <th>{{ translate('Business_Type') }}</th>
                <th>{{ translate('Verification_Status') }}</th>
                <th>{{ translate('Rating') }}</th>
                <th>{{ translate('Total_Bookings') }}</th>
                <th>{{ translate('Is_Featured') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $salon)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ ucwords($salon->business_type ?? 'salon') }}</td>
                    <td>
                        @if($salon->verification_status == 1)
                            {{ translate('Approved') }}
                        @elseif($salon->verification_status == 2)
                            {{ translate('Rejected') }}
                        @else
                            {{ translate('Pending') }}
                        @endif
                    </td>
                    <td>{{ number_format($salon->avg_rating ?? 0, 2) }}</td>
                    <td>{{ $salon->total_bookings ?? 0 }}</td>
                    <td>{{ $salon->is_featured ? translate('messages.Yes') : translate('messages.No') }}</td>
                    <td>{{ ($salon->store->status ?? 0) == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

