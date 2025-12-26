@extends('layouts.admin.app')

@section('title',translate('messages.Salon_List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/rental/provider.png') }}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.Salon')}}
                </span></h1>
            <div class="page-header-select-wrapper">
            </div>
        </div>
        <!-- End Page Header -->


        <!-- Salon Card Wrapper -->
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card card--bg-1">
                    <h4 class="title">{{$totalSalons ?? 0}}</h4>
                    <span class="subtitle">{{translate('messages.total_salons')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/total_provider.png')}}" alt="salon">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card card--bg-3">
                    <h4 class="title">{{$activeSalons ?? 0}}</h4>
                    <span class="subtitle">{{translate('messages.active_salons')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/active_provider.png')}}" alt="salon">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card card--bg-4">
                    <h4 class="title">{{$inactiveSalons ?? 0}}</h4>
                    <span class="subtitle">{{translate('messages.inactive_salons')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/inactive_providers.png')}}" alt="salon">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card card--bg-2">
                    <h4 class="title">{{$newlyJoinedSalons ?? 0}}</h4>
                    <span class="subtitle">{{translate('messages.newly_joined_salons')}}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/new_provider.png')}}" alt="{{translate('salon')}}">
                </div>
            </div>
        </div>

        <ul class="transaction--information text-uppercase">
            <li class="text--info">
                <i class="tio-document-text-outlined"></i>
                <div>
                    <span>{{translate('messages.total_transactions')}}</span> <strong>{{$totalTransaction ?? 0}}</strong>
                </div>
            </li>
            <li class="seperator"></li>
            <li class="text--success">
                <i class="tio-checkmark-circle-outlined success--icon"></i>
                <div>
                    <span>{{translate('messages.commission_earned')}}</span> <strong>{{\App\CentralLogics\Helpers::format_currency($commissionEarned ?? 0)}}</strong>
                </div>
            </li>
            <li class="seperator"></li>
            <li class="text--danger">
                <i class="tio-atm"></i>
                <div>
                    <span>{{translate('messages.total_salon_withdraws')}}</span> <strong>{{\App\CentralLogics\Helpers::format_currency($storeWithdraws ?? 0)}}</strong>
                </div>
            </li>
        </ul>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('messages.salons_list')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$salons->total()}}</span></h5>

                    @if(!isset(auth('admin')->user()->zone_id))
                    <div class="select-item min--280">
                        <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="zone_id">
                            <option value="" {{!request('zone_id')?'selected':''}}>{{ translate('messages.All_Zones') }}</option>
                            @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                                <option
                                    value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>
                                    {{$z['name']}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}" name="search" class="form-control"
                                    placeholder="{{translate('ex_:_Search_salon_Name')}}" aria-label="{{translate('messages.search')}}" >
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
                    @endif


                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40 font-semibold" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.salon.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.salon.export', array_merge(['type' => 'csv'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
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
                        <th class="border-0">{{translate('messages.salon')}}</th>
                        <th class="border-0">{{translate('messages.owner_info')}}</th>
                        <th class="border-0">{{translate('messages.Business_Type')}}</th>
                        <th class="border-0">{{translate('messages.badges')}} / {{translate('messages.priority')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.total_bookings')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.status')}}</th>
                        <th class="text-center border-0">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($salons as $key=>$salon)
                        <tr>
                            <td>{{$key+$salons->firstItem()}}</td>
                            <td>
                                <div>
                                    <a href="{{route('admin.beautybooking.salon.view', $salon->id)}}" class="table-rest-info" alt="{{translate('view salon')}}">
                                    <img class="img--60 circle onerror-image" data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"
                                            src="{{ $salon->store['logo_full_url'] ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                            >
                                        <div class="info"><div title="{{ $salon->store?->name }}" class="text--title">
                                            {{Str::limit($salon->store->name ?? 'N/A',20,'...')}}
                                            </div>
                                            <div class="font-light">
                                                {{translate('messages.id')}}:{{$salon->id}}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </td>

                            <td>
                                @if($salon->store && $salon->store->vendor)
                                <span title="{{ $salon->store->vendor->f_name.' '.$salon->store->vendor->l_name }}" class="d-block font-size-sm text-body">
                                    {{Str::limit($salon->store->vendor->f_name.' '.$salon->store->vendor->l_name,20,'...')}}
                                </span>
                                <div>
                                    <a href="tel:{{ $salon->store->vendor->phone ?? '' }}">
                                        {{$salon->store->vendor->phone ?? 'N/A'}}
                                    </a>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-info">{{ ucfirst($salon->business_type ?? 'salon') }}</span>
                            </td>
                            <td>
                                @php
                                    $activeSubscriptions = $salon->subscriptions ?? collect();
                                    $hasFeaturedSub = $activeSubscriptions->contains(function($sub) {
                                        return in_array($sub->subscription_type, ['featured_listing', 'banner_ads']) && $sub->status === 'active';
                                    });
                                    $hasBoostSub = $activeSubscriptions->contains(function($sub) {
                                        return $sub->subscription_type === 'boost_ads' && $sub->status === 'active';
                                    });
                                    $badges = $salon->badges_list ?? [];
                                @endphp
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($badges as $badge)
                                        <span class="badge badge-soft-success text-capitalize">{{ str_replace('_',' ', $badge) }}</span>
                                    @endforeach
                                    @if(empty($badges))
                                        <span class="badge badge-soft-secondary">{{ translate('messages.none') }}</span>
                                    @endif
                                    @if($salon->is_verified)
                                        <span class="badge badge-soft-primary">{{ translate('messages.verified') }}</span>
                                    @endif
                                    @if($salon->is_featured || $hasFeaturedSub)
                                        <span class="badge badge-soft-info">{{ translate('messages.featured') }}</span>
                                    @endif
                                    @if($hasBoostSub)
                                        <span class="badge badge-soft-warning">{{ translate('messages.boosted') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    // Use eager-loaded counts from withCount() to avoid N+1 queries
                                    // استفاده از تعدادهای eager-loaded از withCount() برای جلوگیری از کوئری‌های N+1
                                    $totalBookings = $salon->total_bookings_count ?? 0;
                                    $completedBookings = $salon->completed_bookings_count ?? 0;
                                    $cancelledBookings = $salon->cancelled_bookings_count ?? 0;
                                    $pendingBookings = $salon->pending_bookings_count ?? 0;
                                    $confirmedBookings = $salon->confirmed_bookings_count ?? 0;
                                    $cancellationRate = $totalBookings > 0 ? ($cancelledBookings / $totalBookings) * 100 : 0;
                                @endphp
                                <span class="form-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="bottom" data-html="true"
                                      data-original-title="<div class='text-left p-3'>
                                <div class='d-flex gap-2'><div class='w--100px'>{{translate('Complete')}}</div> : {{ $completedBookings }}</div>
                              <div class='d-flex gap-2'><div class='w--100px'>{{translate('Confirmed')}}</div> : {{ $confirmedBookings }}</div>
                              <div class='d-flex gap-2'><div class='w--100px'>{{translate('Pending')}}</div> : {{ $pendingBookings }}</div>
                              <div class='d-flex gap-2'><div class='w--100px'>{{translate('Canceled')}}</div> : {{ $cancelledBookings }}</div>
                              <div class='text-danger font-bold d-flex gap-2'><div class='w--100px'>{{translate('Cancelation Rate')}}</div> : {{ number_format($cancellationRate, 2) }}%</div>
                            </div>">
                                      {{ $totalBookings }} <i class="tio-info"></i>
                                </span>
                            </td>

                            <td>
                                @if($salon->verification_status == 1)
                                <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$salon->id}}">
                                    <input type="checkbox" data-url="{{route('admin.beautybooking.salon.status',[$salon->id])}}" data-message="{{translate('messages.you_want_to_change_this_salon_status')}}" class="toggle-switch-input status_change_alert" id="stocksCheckbox{{$salon->id}}" {{$salon->store->status ?? false ?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                @elseif($salon->verification_status == 2)
                                <span class="badge badge-soft-danger">{{translate('messages.rejected')}}</span>
                                @else
                                <span class="badge badge-soft-warning">{{translate('messages.pending')}}</span>
                                @endif
                            </td>

                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--warning btn-outline-warning"
                                            href="{{route('admin.beautybooking.salon.view', $salon->id)}}"
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
                @if(count($salons) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $salons->withQueryString()->links() !!}
                </div>
                @if(count($salons) === 0)
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


    <div class="d-none" id="data-set"
        data-translate-are-you-sure="{{ translate('Are_you_sure?') }}"
        data-translate-no="{{ translate('no') }}"
        data-translate-yes="{{ translate('yes') }}"
    ></div>


@endsection

@push('script_2')
<!-- Beauty Error Handler -->
<script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-error-handler.js') }}"></script>
<script src="{{asset('Modules/BeautyBooking/public/assets/js/admin/view-pages/salon-list.js')}}"></script>
@endpush

