@php
    $all = $salon->bookings()->count();
    $completed = $salon->bookings()->where('status', 'completed')->count();
    $cancelled = $salon->bookings()->where('status', 'cancelled')->count();
    $pending = $salon->bookings()->where('status', 'pending')->count();
@endphp

<!-- Page Heading -->
@if($salon->store && $salon->store->vendor && $salon->store->vendor->status)
    <div class="row g-3 text-capitalize">
        <!-- Earnings Card Example -->
        <div class="col-md-4">
            <div class="card h-100 card--bg-1">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                    <h5 class="cash--subtitle text-white">
                        {{translate('messages.total_earnings')}}
                    </h5>
                    <div class="d-flex align-items-center justify-content-center mt-3">
                        <div class="cash-icon mr-3">
                            <img src="{{asset('public/assets/admin/img/cash.png')}}" alt="img">
                        </div>
                        <h2 class="cash--title text-white">{{\App\CentralLogics\Helpers::format_currency($wallet->total_earning ?? 0)}}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row g-3">
                <!-- Pending Withdraw Card Example -->
                <div class="col-sm-6">
                    <div class="resturant-card card--bg-2">
                        <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->pending_withdraw ?? 0)}}</h4>
                        <div class="subtitle">{{translate('messages.pending_withdraw')}}</div>
                        <img class="resturant-icon w--30"
                             src="{{asset('public/assets/admin/img/transactions/pending.png')}}"
                             alt="transaction">
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="resturant-card card--bg-3">
                        <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_withdrawn ?? 0)}}</h4>
                        <div class="subtitle">{{translate('messages.total_withdrawal_amount')}}</div>
                        <img class="resturant-icon w--30"
                             src="{{asset('public/assets/admin/img/transactions/withdraw-amount.png')}}"
                             alt="transaction">
                    </div>
                </div>

                <!-- Withdrawable Balance Card Example -->
                <div class="col-sm-6">
                    <div class="resturant-card card--bg-4">
                        <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(($wallet->balance ?? 0) > 0 ? ($wallet->balance ?? 0) : 0)}}</h4>
                        <div class="subtitle">{{translate('messages.withdraw_able_balance')}}</div>
                        <img class="resturant-icon w--30"
                             src="{{asset('public/assets/admin/img/transactions/withdraw-balance.png')}}"
                             alt="transaction">
                    </div>
                </div>

                <!-- Total Bookings Card Example -->
                <div class="col-sm-6">
                    <div class="resturant-card card--bg-1">
                        <h4 class="title">{{ $all }}</h4>
                        <div class="subtitle">{{translate('messages.total_bookings')}}</div>
                        <img class="resturant-icon w--30"
                             src="{{asset('public/assets/admin/img/total_order.png')}}"
                             alt="booking">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card mt-4 p-4">
    <div class="row g-2" id="booking_stats">
        <div class="col-lg-3 col-sm-6">
            <!-- Card -->
            <a class="order--card h-100" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'bookings'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        {{translate('All')}}
                    </h6>
                    <span class="card-title text--info">
                        {{ $all }}
                    </span>
                </div>
            </a>
            <!-- End Card -->
        </div>
        <div class="col-lg-3 col-sm-6">
            <!-- Card -->
            <a class="order--card h-100" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'bookings', 'status' => 'completed'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        {{translate('Completed')}}
                    </h6>
                    <span class="card-title text--success">
                        {{ $completed }}
                    </span>
                </div>
            </a>
            <!-- End Card -->
        </div>
        <div class="col-lg-3 col-sm-6">
            <!-- Card -->
            <a class="order--card h-100" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'bookings', 'status' => 'cancelled'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        {{translate('Cancelled')}}
                    </h6>
                    <span class="card-title text--danger">
                        {{ $cancelled }}
                    </span>
                </div>
            </a>
            <!-- End Card -->
        </div>
        <div class="col-lg-3 col-sm-6">
            <!-- Card -->
            <a class="order--card h-100" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'bookings', 'status' => 'pending'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        {{translate('Pending')}}
                    </h6>
                    <span class="card-title text--warning">
                        {{ $pending }}
                    </span>
                </div>
            </a>
            <!-- End Card -->
        </div>
    </div>
</div>

<div class="taxi-banner radius-10 mt-4 mb-20"
     style="background-image: url('{{ $salon->store->cover_photo_full_url ?? asset('public/assets/admin/img/100x100/1.png') }}'); background-repeat: no-repeat; background-position: center; background-size: cover;">
    <div class="taxi-info-wrapper d-flex flex-wrap flex-sm-nowrap gap-30px">
        <div class="logo">
            <img data-onerror-image="{{asset('public/assets/admin/img/100x100/1.png')}}"
                 src="{{ $salon->store->logo_full_url ?? asset('public/assets/admin/img/100x100/1.png') }}" width="150" class="rounded-8"
                 alt="">
        </div>
        <div class="taxi-info">
            <h3 class="fs-20 fw-bold text--title mb-20"> {{ $salon->store->name ?? 'N/A' }}</h3>
            <div class="details d-flex flex-wrap flex-column flex-sm-row gap-40px">
                <div class="details-single d-flex align-items-center gap-2">
                    <img src="{{ asset('public/assets/admin/img/icons/zone.png') }}" width="36" height="36"
                         class="rounded" alt="">
                    <div>
                        <h5 class="lh--12 mb-0 color-3C3C3C"> {{ translate('messages.Business_zone') }}
                        </h5>
                        <span class="fs-13 lh--12 color-484848">{{$salon->zone?->name ?? 'N/A'}}</span>
                    </div>
                </div>
                <div class="details-single d-flex align-items-center gap-2">
                    <img src="{{ asset('public/assets/admin/img/icons/job-type.png') }}" width="36"
                         height="36" class="rounded" alt="">
                    <div>
                        <h5 class="lh--12 mb-0 color-3C3C3C"> {{ translate('messages.Business_Type') }}
                        </h5>
                        <span class="fs-13 lh--12 color-484848">{{ ucwords($salon->business_type ?? 'salon') }}</span>
                    </div>
                </div>
                <div class="details-single d-flex align-items-center gap-2">
                    <img src="{{ asset('public/assets/admin/img/icons/vehicle-type.png') }}" width="36"
                         height="36" class="rounded" alt="">
                    <div>
                        <h5 class="lh--12 mb-0 color-3C3C3C"> {{ translate('messages.Average_Rating') }}
                        </h5>
                        <span class="fs-13 lh--12 color-484848">{{ number_format($salon->avg_rating ?? 0, 1) }}</span>
                    </div>
                </div>
                <div class="details-single d-flex align-items-center gap-2">
                    <img src="{{ asset('public/assets/admin/img/icons/vehicle-type.png') }}" width="36"
                         height="36" class="rounded" alt="">
                    <div>
                        <h5 class="lh--12 mb-0 color-3C3C3C"> {{ translate('messages.Total_Reviews') }}
                        </h5>
                        <span class="fs-13 lh--12 color-484848">{{ $salon->total_reviews ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h5 class="text-title mb-1">
                {{ translate('messages.Salon_Information') }}
            </h5>
            <p class="fs-12">
                {{ translate('messages.Here you can see all the information that salon submit during registration') }}
            </p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card __bg-FAFAFA border-0 h-100">
                    <div class="card-body">
                        <h5 class="mb-10px font-bold"> {{ translate('messages.General_Information') }}
                        </h5>
                        <div class="resturant--info-address">
                            <ul class="address-info address-info-2 p-0 text-dark">
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Salon Name') }}</span>
                                    <span>: {{$salon->store?->name ?? 'N/A'}}</span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Business Address') }}</span>
                                    <div>
                                        <div class="short-description">
                                            <span>: {{ Str::limit($salon->store->address ?? 'N/A', 500) }} </span>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Business Type') }}</span>
                                    <span>: {{ ucwords($salon->business_type ?? 'salon') }}</span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.License Number') }}</span>
                                    <span>: {{ $salon->license_number ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card __bg-FAFAFA border-0 h-100">
                    <div class="card-body">
                        <h5 class="mb-10px font-bold"> {{ translate('messages.Owner_Information') }}
                        </h5>
                        <div class="resturant--info-address">
                            <ul class="address-info address-info-2 p-0 text-dark">
                                @if($salon->store && $salon->store->vendor)
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.First Name') }}</span>
                                    <span>: {{$salon->store->vendor->f_name ?? 'N/A'}} </span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Last Name') }}</span>
                                    <span>: {{$salon->store->vendor->l_name ?? 'N/A'}}</span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Phone') }}</span>
                                    <span>: {{$salon->store->vendor->phone ?? 'N/A'}}</span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="label min-w-sm-auto">{{ translate('messages.Email') }}</span>
                                    <span>: {{ $salon->store->vendor->email ?? 'N/A' }}</span>
                                </li>
                                @else
                                <li class="d-flex align-items-start">
                                    <span class="text-muted">{{ translate('messages.No vendor information available') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

