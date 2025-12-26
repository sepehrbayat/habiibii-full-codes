
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Package_List') }}</h1></div>
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
                <th>{{ translate('Package_Name') }}</th>
                <th>{{ translate('Salon') }}</th>
                <th>{{ translate('Service') }}</th>
                <th>{{ translate('Price') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $package)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $package->name }}</td>
                    <td>{{ $package->salon->store->name ?? translate('N/A') }}</td>
                    <td>{{ $package->service->name ?? translate('N/A') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($package->price ?? 0) }}</td>
                    <td>{{ $package->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

