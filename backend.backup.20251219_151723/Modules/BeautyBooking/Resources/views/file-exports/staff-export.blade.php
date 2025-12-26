
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate($data['fileName'] ? $data['fileName'] : 'Staff_List') }}</h1></div>
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
                <th>{{ translate('Staff_Name') }}</th>
                <th>{{ translate('Email') }}</th>
                <th>{{ translate('Phone') }}</th>
                <th>{{ translate('Specializations') }}</th>
                <th>{{ translate('Status') }}</th>
            </thead>
            <tbody>
            @foreach($data['data'] as $key => $staff)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email ?? translate('N/A') }}</td>
                    <td>{{ $staff->phone ?? translate('N/A') }}</td>
                    <td>{{ is_array($staff->specializations) ? implode(', ', $staff->specializations) : ($staff->specializations ?? translate('N/A')) }}</td>
                    <td>{{ $staff->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

