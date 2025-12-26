
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Retail_Product_List') }}</h1></div>
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
                <th>{{ translate('Product_Name') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Category') }}</th>
                <th>{{ translate('Price') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $product)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ $product->category ?? translate('N/A') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($product->price ?? 0) }}</td>
                    <td>{{ $product->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

