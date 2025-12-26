
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Booking_List') }}</h1></div>
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
                <th>{{ translate('Customer') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Service') }}</th>
                <th>{{ translate('Booking_Date') }}</th>
                <th>{{ translate('Booking_Time') }}</th>
                <th>{{ translate('Total_Amount') }}</th>
                <th>{{ translate('Status') }}</th>
                <th>{{ translate('Payment_Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $booking)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $booking->user->f_name ?? '' }} {{ $booking->user->l_name ?? '' }}</td>
                    <td>{{ $booking->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ $booking->service->name ?? translate('N/A') }}</td>
                    <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') : translate('N/A') }}</td>
                    <td>{{ $booking->booking_time ?? translate('N/A') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($booking->total_amount ?? 0) }}</td>
                    <td>{{ ucwords($booking->status ?? 'pending') }}</td>
                    <td>{{ ucwords($booking->payment_status ?? 'unpaid') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

