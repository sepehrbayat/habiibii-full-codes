
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Service_List') }}</h1></div>
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
                <th>{{ translate('Service_Name') }}</th>
                <th>{{ translate('Category') }}</th>
                <th>{{ translate('Duration') }}</th>
                <th>{{ translate('Price') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $service)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->category->name ?? translate('N/A') }}</td>
                    <td>{{ $service->duration_minutes ?? 0 }} {{ translate('minutes') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($service->price ?? 0) }}</td>
                    <td>{{ $service->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

