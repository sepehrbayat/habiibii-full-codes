<!-- Staff Tab -->
<div class="card">
    <div class="card-header py-2">
        <div class="search--button-wrapper">
            <h5 class="card-title">{{translate('messages.staff_list')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$staff->total()}}</span></h5>
        </div>
    </div>
    <div class="table-responsive datatable-custom">
        <table id="columnSearchDatatable"
                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                data-hs-datatables-options='{
                    "order": [],
                    "orderCellsTop": true,
                    "paging":false
                }'>
            <thead class="thead-light">
                <tr>
                    <th class="border-0">{{translate('sl')}}</th>
                    <th class="border-0">{{translate('messages.name')}}</th>
                    <th class="border-0">{{translate('messages.specialization')}}</th>
                    <th class="border-0">{{translate('messages.phone')}}</th>
                    <th class="border-0">{{translate('messages.status')}}</th>
                </tr>
            </thead>
            <tbody id="set-rows">
                @foreach($staff ?? [] as $key=>$member)
                    <tr>
                        <td>{{$key+(isset($staff) && method_exists($staff, 'firstItem') ? $staff->firstItem() : 0)}}</td>
                        <td><span class="text--title">{{ Str::limit($member->name, 20) }}</span></td>
                        <td><span class="text--title">{{ Str::limit(implode(', ', $member->specializations ?? []), 30) }}</span></td>
                        <td><span class="text--title">{{ $member->phone ?? 'N/A' }}</span></td>
                        <td>
                            <span class="badge badge-{{ $member->status ? 'success' : 'danger' }}">
                                {{ $member->status ? translate('messages.active') : translate('messages.inactive') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(isset($staff) && count($staff) !== 0)
    <hr>
    @endif
    @if(isset($staff))
        <div class="page-area">
            {!! $staff->withQueryString()->links() !!}
        </div>
    @endif
    @if(isset($staff) && count($staff) === 0)
    <div class="empty--data">
        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
        <h5>
            {{translate('no_data_found')}}
        </h5>
    </div>
    @endif
</div>

