
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Gift_Card_List') }}</h1></div>
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
                <th>{{ translate('Gift_Card_Code') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Amount') }}</th>
                <th>{{ translate('Purchaser') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $giftCard)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $giftCard->code }}</td>
                    <td>{{ $giftCard->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($giftCard->amount ?? 0) }}</td>
                    <td>{{ $giftCard->purchaser->f_name ?? '' }} {{ $giftCard->purchaser->l_name ?? '' }}</td>
                    <td>{{ ucwords($giftCard->status ?? 'pending') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

