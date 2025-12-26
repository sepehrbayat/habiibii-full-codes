
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Review_List') }}</h1></div>
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
                <th>{{ translate('Rating') }}</th>
                <th>{{ translate('Comment') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $review)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $review->user->f_name ?? '' }} {{ $review->user->l_name ?? '' }}</td>
                    <td>{{ $review->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ $review->rating ?? 0 }}</td>
                    <td>{{ Str::limit($review->comment ?? '', 50) }}</td>
                    <td>{{ ucwords($review->status ?? 'pending') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

