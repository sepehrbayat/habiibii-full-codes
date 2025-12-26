@extends('layouts.admin.app')

@section('title', translate('messages.service_list'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/rental/veh.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.service_list') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="" method="get">
                    <div class="row g-2 mb-20">
                        <div class="col-sm-6 col-md-4">
                            <div class="select-item">
                                <label for="salon-select" class="input-label">{{ translate('messages.Salon') }}</label>
                                <select id="salon-select" class="select-30 js-data-example-ajax form-control set-filter opacity-70"
                                        name="salon_id">
                                    <option value="" selected disabled>{{ translate('messages.select_salon') }}
                                    </option>
                                    @foreach($salons as $salon)
                                        <option value="{{ $salon->id }}" {{request()->salon_id == $salon->id ? 'selected' : ''}}>{{ $salon->store->name ?? 'N/A'}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="select-item">
                                <label for="category-select" class="input-label">{{ translate('messages.category') }}</label>
                                <select id="category-select" class="js-data-example-ajax form-control set-filter opacity-70"
                                        name="category_id">
                                    <option value="" selected disabled>{{ translate('messages.select_category') }}
                                    </option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{request()->category_id == $category->id ? 'selected' : ''}}>{{ $category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="select-item">
                                <label for="status-select" class="input-label">{{ translate('messages.status') }}</label>
                                <select id="status-select" class="js-data-example-ajax form-control set-filter opacity-70"
                                        name="status">
                                    <option value="" selected disabled>
                                        {{ translate('messages.select_status') }}
                                    </option>
                                    <option value="1" {{request()->status == '1' ? 'selected' : ''}}>{{ translate('messages.Active') }}</option>
                                    <option value="0" {{request()->status == '0' ? 'selected' : ''}}>{{ translate('messages.Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end mt-4">
                                <button type="reset" id="reset_btn"
                                        class="btn btn--reset min-w-120px">{{ translate('messages.reset') }}</button>
                                <button type="submit"
                                        class="btn btn--primary min-w-120px">{{ translate('messages.filter') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.Total_Services') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $services->total() }}</span>
                    </h5>
                    <form class="search-form flex-grow-1 max-w-353px">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                                   name="search" class="form-control"
                                   placeholder="{{ translate('Search by name, salon info...') }}"
                                   aria-label="{{ translate('messages.Search by name, salon info...') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i
                                    class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    @if (request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                data-url="{{ url()->full() }}">{{ translate('messages.reset') }}</button>
                    @endif


                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40 font-semibold"
                           href="javascript:;"
                           data-hs-unfold-options='{
                    "target": "#usersExportDropdown",
                    "type": "css-animation"
                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item"
                               href="{{ route('admin.beautybooking.service.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                               href="{{ route('admin.beautybooking.service.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
                    <a class="btn btn--primary font-weight-bold float-right mr-2 mb-0"
                       href="{{ route('admin.beautybooking.service.create') }}">{{ translate('messages.new_service') }}
                    </a>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
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
                        <th class="border-0">{{ translate('sl') }}</th>
                        <th class="border-0">{{ translate('messages.Service_Info') }}</th>
                        <th class="border-0">{{ translate('messages.Salon') }}</th>
                        <th class="border-0">{{ translate('messages.Category') }}</th>
                        <th class="border-0">{{ translate('messages.related_services') }}</th>
                        <th class="border-0">{{ translate('messages.Duration') }}</th>
                        <th class="border-0">{{ translate('messages.Price') }}</th>
                        <th class="border-0">{{ translate('messages.Total_Bookings') }}</th>
                        <th class="text-center border-0">{{ translate('messages.Status') }}</th>
                        <th class="text-center border-0">{{ translate('messages.Action') }}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($services as $key => $service)
                        <tr>
                            <td>{{$key+$services->firstItem()}}</td>
                            <td>
                                <div class="text--title">
                                    <a href="{{ route('admin.beautybooking.service.details', $service->id)}}?service_list=true" class="font-medium">
                                        {{ $service->name }}
                                    </a>
                                    <div class="opacity-lg">
                                        {{ Str::limit($service->description, 50) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text--title">
                                    <a href="{{ route('admin.beautybooking.salon.view', $service->salon_id)}}" class="font-medium">
                                        {{ $service->salon->store->name ?? translate('salon_not_found') }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ $service->category->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $relations = $service->activeRelations ?? collect();
                                    $complementaryCount = $relations->where('relation_type', 'complementary')->count();
                                    $crossSellCount = $relations->where('relation_type', 'cross_sell')->count();
                                    $upsellCount = $relations->where('relation_type', 'upsell')->count();
                                @endphp
                                <div class="d-flex flex-wrap gap-1">
                                    @if($complementaryCount)
                                        <span class="badge badge-soft-info">
                                            {{ translate('messages.complementary') }}: {{ $complementaryCount }}
                                        </span>
                                    @endif
                                    @if($upsellCount)
                                        <span class="badge badge-soft-success">
                                            {{ translate('messages.upsell') }}: {{ $upsellCount }}
                                        </span>
                                    @endif
                                    @if($crossSellCount)
                                        <span class="badge badge-soft-primary">
                                            {{ translate('messages.cross_sell') }}: {{ $crossSellCount }}
                                        </span>
                                    @endif
                                    @if(!$complementaryCount && !$upsellCount && !$crossSellCount)
                                        <span class="badge badge-soft-secondary">{{ translate('messages.none') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ $service->duration_minutes }} {{ translate('messages.minutes') }}
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ \App\CentralLogics\Helpers::format_currency($service->price) }}
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ $service->bookings()->count() }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$service->id}}">
                                        <input type="checkbox" data-url="{{route('admin.beautybooking.service.status',[$service['id'],$service->status?0:1])}}"
                                               class="toggle-switch-input redirect-url" id="stocksCheckbox{{$service->id}}" {{$service->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{ route('admin.beautybooking.service.details', $service->id)}}?service_list=true"
                                       title="{{ translate('messages.view') }}"><i class="tio-visible-outlined"></i>
                                    </a>
                                    <a class="btn action-btn btn-outline-primary" href="{{ route('admin.beautybooking.service.edit', $service->id)}}"
                                       title="{{ translate('messages.edit') }}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="service-{{$service['id']}}" data-message="{{ translate('Want to delete this service') }}" title="{{translate('messages.delete_service')}}"><i
                                            class="tio-delete-outlined"></i>
                                    </a>

                                </div>
                                <form action="{{route('admin.beautybooking.service.delete',[$service['id']])}}" method="post" id="service-{{$service->id}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
            @if(count($services) !== 0)
                <hr>
            @endif
            <div class="page-area">
                {!! $services->appends($_GET)->links() !!}
            </div>
            @if(count($services) === 0)
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


@push('script_2')
<script src="{{ asset('Modules/Rental/public/assets/js/admin/view-pages/vehicle-list.js') }}"></script>
@endpush

