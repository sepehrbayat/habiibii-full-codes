@extends('layouts.admin.app')

@section('title', translate('Package List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/items.png') }}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.Package_List')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('messages.Package_List')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$packages->total()}}</span></h5>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" name="search" value="{{ request()?->search ?? null }}" type="search" class="form-control min-height-45" placeholder="{{translate('ex_:_Search_package')}}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary min-height-45"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
                    @endif

                    @if(!isset(auth('admin')->user()->zone_id))
                    <div class="select-item min--280">
                        <select name="salon_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="salon_id">
                            <option value="" {{!request('salon_id')?'selected':''}}>{{ translate('messages.All_Salons') }}</option>
                            @foreach($salons ?? [] as $salon)
                                <option value="{{ $salon->id }}" {{request('salon_id') == $salon->id?'selected':''}}>
                                    {{ $salon->store->name ?? 'Salon #' . $salon->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="select-item min--280">
                        <select name="status" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="status">
                            <option value="" {{!request('status')?'selected':''}}>{{ translate('messages.All_Status') }}</option>
                            <option value="1" {{request('status') == '1'?'selected':''}}>{{ translate('messages.Active') }}</option>
                            <option value="0" {{request('status') == '0'?'selected':''}}>{{ translate('messages.Inactive') }}</option>
                        </select>
                    </div>
                    @endif

                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.package.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.package.export', array_merge(['type' => 'csv'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
            <!-- End Header -->
            <div class="card-body p-0">
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
                                <th class="border-0">{{ translate('messages.Name') }}</th>
                                <th class="border-0">{{ translate('messages.Salon') }}</th>
                                <th class="border-0">{{ translate('messages.Service') }}</th>
                                <th class="border-0">{{ translate('messages.Sessions') }}</th>
                                <th class="border-0">{{ translate('messages.Price') }}</th>
                                <th class="border-0">{{ translate('messages.Discount') }}</th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="text-center border-0">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($packages as $key=>$package)
                                <tr>
                                    <td>{{$key+$packages->firstItem()}}</td>
                                    <td><span class="text--title">{{ $package->name }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($package->salon->store->name ?? 'N/A', 20) }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($package->service->name ?? 'N/A', 20) }}</span></td>
                                    <td><span class="text--title">{{ $package->sessions_count }}</span></td>
                                    <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($package->total_price) }}</span></td>
                                    <td><span class="text--title">{{ number_format($package->discount_percentage, 1) }}%</span></td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$package->id}}">
                                            <input type="checkbox" data-url="{{route('admin.beautybooking.package.status',[$package->id])}}" class="toggle-switch-input status_change_alert" id="statusCheckbox{{$package->id}}" {{$package->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a href="{{route('admin.beautybooking.package.view', $package->id)}}" class="btn action-btn btn--warning btn-outline-warning" title="{{ translate('messages.details') }}">
                                                <i class="tio-visible-outlined"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($packages) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $packages->withQueryString()->links() !!}
                </div>
                @if(count($packages) === 0)
                <div class="empty--data">
                    <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>
@endsection

