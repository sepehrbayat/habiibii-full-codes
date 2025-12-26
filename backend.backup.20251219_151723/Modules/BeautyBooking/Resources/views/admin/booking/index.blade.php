@extends('layouts.admin.app')

@section('title', translate('messages.all_bookings'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.All_Bookings') }}</span>
                    </h1>
                </div>
            </div>
            <div class="mt-3">
                <ul class="nav nav-pills">
                    @php
                        $statusTabs = [
                            'all' => translate('messages.All'),
                            'pending' => translate('messages.pending'),
                            'confirmed' => translate('messages.confirmed'),
                            'completed' => translate('messages.completed'),
                            'cancelled' => translate('messages.cancelled'),
                        ];
                    @endphp
                    @foreach ($statusTabs as $key => $label)
                        <li class="nav-item mr-2 mb-2">
                            <a class="nav-link {{ $status === $key ? 'active' : '' }}"
                               href="{{ route('admin.beautybooking.booking.list', array_merge(request()->except('page'), ['status' => $key])) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.All_Bookings') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $bookings->total() }}</span>
                    </h5>
                    <form action="" method="get" class="search-form flex-grow-1 max-w-353px">
                        <!-- Search -->
                        <input type="hidden" value="{{request()?->status  }}" name="status" >
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                                   name="search" class="form-control"
                                   placeholder="{{ translate('Search by booking reference, customer name, email') }}"
                                   aria-label="{{ translate('messages.Search by booking reference, customer name, email') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i class="tio-search"></i></button>

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
                               href="{{ route('admin.beautybooking.booking.export', array_merge(['type' => 'excel'], request()->query())) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                               href="{{ route('admin.beautybooking.booking.export', array_merge(['type' => 'csv'], request()->query())) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white h--40px filter-button-show" href="javascript:;">
                            <i class="tio-filter-list mr-1"></i> {{ translate('messages.filter') }} <span class="badge badge-success badge-pill ml-1" id="filter_count"></span>
                        </a>
                    </div>
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
                        <th class="border-0">{{translate('sl')}}</th>
                        <th class="border-0">{{translate('messages.Reference')}}</th>
                        <th class="border-0">{{translate('messages.Customer')}}</th>
                        <th class="border-0">{{translate('messages.Salon')}}</th>
                        <th class="border-0">{{translate('messages.Service')}}</th>
                        <th class="border-0">{{translate('messages.Date_Time')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.Amount')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.status')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.Payment')}}</th>
                        <th class="text-center border-0">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($bookings as $key=>$booking)
                        <tr>
                            <td>{{$key+$bookings->firstItem()}}</td>
                            <td>
                                <span class="text--title">{{ $booking->booking_reference }}</span>
                            </td>
                            <td>
                                <span class="text--title">{{ ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '') }}</span>
                            </td>
                            <td>
                                <span class="text--title">{{ Str::limit($booking->salon->store->name ?? 'N/A', 20) }}</span>
                            </td>
                            <td>
                                <span class="text--title">{{ Str::limit($booking->service->name ?? 'N/A', 20) }}</span>
                            </td>
                            <td>
                                <span class="text--title">{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</span>
                            </td>
                            <td>
                                <span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : ($booking->status == 'completed' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $booking->payment_status == 'paid' ? 'success' : ($booking->payment_status == 'partially_paid' ? 'warning' : 'danger') }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--warning btn-outline-warning"
                                            href="{{route('admin.beautybooking.booking.view', $booking->id)}}"
                                            title="{{ translate('messages.details') }}"><i
                                                class="tio-visible-outlined"></i>
                                        </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
                @if(count($bookings) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $bookings->withQueryString()->links() !!}
                </div>
                @if(count($bookings) === 0)
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

    <div id="datatableFilterSidebar" class="hs-unfold-content_ sidebar sidebar-bordered sidebar-box-shadow initial-hidden">
        <div class="card card-lg sidebar-card sidebar-footer-fixed">
            <div class="card-header">
                <h4 class="card-header-title">{{translate('messages.Booking_filter')}}</h4>

                <!-- Toggle Button -->
                <a class="js-hs-unfold-invoker_ btn btn-icon btn-sm btn-ghost-dark ml-2 filter-button-hide" href="javascript:;">
                    <i class="tio-clear tio-lg"></i>
                </a>
                <!-- End Toggle Button -->
            </div>
            @php
                $filterCount = 0;
                if(isset($zone_ids) && count($zone_ids) > 0) $filterCount += 1;
                if(isset($salon_ids) && count($salon_ids)>0) $filterCount += 1;
                if($status == 'all')
                {
                    if(isset($bookingStatus) && count($bookingStatus) > 0) $filterCount += 1;
                }
                if(isset($from_date) && isset($to_date)) $filterCount += 1;
            @endphp
                <!-- Body -->
            <form class="card-body sidebar-body sidebar-scrollbar" action="" method="get" id="booking_filter_form">
                <input type="hidden" name="status" value="{{ request()->status }}">
                <small class="text-cap mb-3">{{translate('messages.zone')}}</small>

                <div class="mb-2 initial--21">
                    <select name="zone_ids[]" id="zone_ids" class="form-control js-select2-custom" multiple="multiple">
                        @foreach(\App\Models\Zone::get(['id','name']) as $zone)
                            <option value="{{$zone->id}}" {{isset($zone_ids)?(in_array($zone->id, $zone_ids)?'selected':''):''}}>{{$zone->name}}</option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">
                <small class="text-cap mb-3">{{translate('messages.Salon')}}</small>
                <div class="mb-2 initial--21">
                    <select name="salon_ids[]" id="salon_ids" class="form-control js-select2-custom" multiple="multiple">
                        @foreach(\Modules\BeautyBooking\Entities\BeautySalon::with('store')->get(['id']) as $salon)
                            <option value="{{$salon->id}}"
                                    @if(isset($salon_ids) && in_array($salon->id, $salon_ids))
                                        selected
                                    @endif>
                                {{$salon->store->name ?? 'Salon #' . $salon->id}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">
                @if($status == 'all')
                    <small class="text-cap mb-3">{{translate('messages.Booking_status')}}</small>

                    <!-- Custom Checkbox -->
                    <div class="custom-control custom-radio mb-2">
                        <input type="checkbox" id="bookingStatus1" name="bookingStatus[]" class="custom-control-input" value="pending" {{isset($bookingStatus)?(in_array('pending', $bookingStatus)?'checked':''):''}}>
                        <label class="custom-control-label" for="bookingStatus1">{{translate('messages.pending')}}</label>
                    </div>
                    <div class="custom-control custom-radio mb-2">
                        <input type="checkbox" id="bookingStatus2" name="bookingStatus[]" class="custom-control-input" value="confirmed" {{isset($bookingStatus)?(in_array('confirmed', $bookingStatus)?'checked':''):''}}>
                        <label class="custom-control-label" for="bookingStatus2">{{translate('messages.confirmed')}}</label>
                    </div>
                    <div class="custom-control custom-radio mb-2">
                        <input type="checkbox" id="bookingStatus3" name="bookingStatus[]" class="custom-control-input" value="completed" {{isset($bookingStatus)?(in_array('completed', $bookingStatus)?'checked':''):''}}>
                        <label class="custom-control-label" for="bookingStatus3">{{translate('messages.Completed')}}</label>
                    </div>
                    <div class="custom-control custom-radio mb-2">
                        <input type="checkbox" id="bookingStatus4" name="bookingStatus[]" class="custom-control-input" value="cancelled" {{isset($bookingStatus)?(in_array('cancelled', $bookingStatus)?'checked':''):''}}>
                        <label class="custom-control-label" for="bookingStatus4">{{translate('messages.cancelled')}}</label>
                    </div>
                @endif

                <hr class="my-4">

                <small class="text-cap mb-3">{{translate('messages.date_between')}}</small>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group m-0">
                            <input type="date" name="from_date" class="form-control" id="date_from" value="{{isset($from_date)?$from_date:''}}">
                        </div>
                    </div>
                    <div class="col-12 text-center">----{{ translate('messages.to') }}----</div>
                    <div class="col-12">
                        <div class="form-group">
                            <input type="date" name="to_date" class="form-control" id="date_to" value="{{isset($to_date)?$to_date:''}}">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer sidebar-footer">
                    <div class="row gx-2">
                        <div class="col">
                            <button type="reset" data-url="{{route('admin.beautybooking.booking.list')}}" class="btn btn-block btn-white" id="reset">{{ translate('Clear all filters') }}</button>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-block btn-primary">{{ translate('messages.save') }}</button>
                        </div>
                    </div>
                </div>
                <!-- End Footer -->
            </form>
        </div>
    </div>
    <input type="hidden" id="get-default-filter-count" value="{{ $filterCount > 0 ? $filterCount : '' }}">
@endsection


@push('script_2')
    <!-- Beauty Error Handler -->
    <script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-error-handler.js') }}"></script>
    <script src="{{asset('public/assets/admin')}}/js/view-pages/order-list.js"></script>
    <script src="{{asset('Modules/BeautyBooking/public/assets/js/admin/view-pages/booking-list.js')}}"></script>

@endpush

