<!-- Services Tab -->
<div class="card">
    <div class="card-header py-2">
        <div class="search--button-wrapper">
            <h5 class="card-title">{{translate('messages.services')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$services->total()}}</span></h5>
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
                    <th class="border-0">{{translate('messages.category')}}</th>
                    <th class="border-0">{{translate('messages.price')}}</th>
                    <th class="border-0">{{translate('messages.duration')}}</th>
                    <th class="border-0">{{translate('messages.status')}}</th>
                </tr>
            </thead>
            <tbody id="set-rows">
                @foreach($services ?? [] as $key=>$service)
                    <tr>
                        <td>{{$key+(isset($services) && method_exists($services, 'firstItem') ? $services->firstItem() : 0)}}</td>
                        <td><span class="text--title">{{ Str::limit($service->name, 20) }}</span></td>
                        <td><span class="text--title">{{ Str::limit($service->category->name ?? 'N/A', 20) }}</span></td>
                        <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($service->price) }}</span></td>
                        <td><span class="text--title">{{ $service->duration_minutes }} {{ translate('messages.minutes') }}</span></td>
                        <td>
                            <span class="badge badge-{{ $service->status ? 'success' : 'danger' }}">
                                {{ $service->status ? translate('messages.active') : translate('messages.inactive') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(isset($services) && count($services) !== 0)
    <hr>
    @endif
    @if(isset($services))
        <div class="page-area">
            {!! $services->withQueryString()->links() !!}
        </div>
    @endif
    @if(isset($services) && count($services) === 0)
    <div class="empty--data">
        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
        <h5>
            {{translate('no_data_found')}}
        </h5>
    </div>
    @endif
</div>

