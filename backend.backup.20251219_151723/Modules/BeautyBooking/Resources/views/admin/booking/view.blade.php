@extends('layouts.admin.app')

@section('title', translate('Booking Details'))

@push('css_or_js')
  <link rel="stylesheet" href="{{ asset('Modules/BeautyBooking/public/assets/css/admin/booking-details.css') }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('/public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Booking_Details') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- Page Header -->

        <div class="row flex-xl-nowrap" id="printableArea">
            <div class="col-lg-8 order-print-area-left">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header align-items-stretch flex-column border-0 pb-0">
                        <div class="d-flex align-items-start justify-content-between flex-wrap mb-2">
                            <div class="order-invoice-left d-flex d-sm-block justify-content-between">
                                <div>
                                    <h1 class="page-header-title d-flex align-items-center __gap-5px">
                                        {{translate('messages.Booking_ID')}} # {{ $booking->booking_reference }}
                                        @if ($booking->edited)
                                        <span class="badge badge--pending text-capitalize">
                                            {{ translate('messages.edited') }}
                                        </span>
                                        @endif
                                    </h1>
                                    <span class="mt-2 d-block d-flex align-items-center __gap-5px">
                                        {{ translate('messages.Placed_on') }} {{ \App\CentralLogics\Helpers::time_date_format($booking->created_at) }}
                                    </span>
                                    <div class="fs-14 text-title mt-2 pt-1 mb-2 d-flex align-items-center __gap-5px">
                                        <span>{{translate('messages.Salon')}}</span> <span>:</span>
                                        <span class="font-bold">{{ $booking->salon->store->name ?? 'N/A' }}</span>
                                        <a href="{{ route('admin.beautybooking.salon.view', $booking->salon_id) }}" class="btn btn--primary-light px-2 py-1 shadow-none">
                                            <i class="tio-visible"></i> {{translate('messages.View_Salon')}}
                                        </a>
                                    </div>
                                    <div class="fs-14 text-title mt-2 pt-1 mb-2 d-flex align-items-center __gap-5px">
                                        <span>{{translate('messages.Customer')}}</span> <span>:</span>
                                        <span class="font-bold">{{ ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '') }}</span>
                                        <span class="text-muted">({{ $booking->user->phone ?? 'N/A' }})</span>
                                    </div>
                                    <div class="fs-14 text-title mt-2 pt-1 mb-2 d-flex align-items-center __gap-5px">
                                        <span>{{translate('messages.Booking_Date_Time')}}</span> <span>:</span>
                                        <span class="font-bold">{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</span>
                                    </div>
                                </div>
                                <div class="d-sm-none">
                                    <a class="btn btn--primary print--btn font-regular d-flex align-items-center __gap-5px"
                                       href="{{route('admin.beautybooking.booking.generate-invoice',["id" => $booking->id])}}">
                                        <i class="tio-print mr-sm-1"></i>
                                        <span>{{ translate('messages.print_invoice') }}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="order-invoice-right mt-3 mt-sm-0">
                                <div class="btn--container ml-auto align-items-center justify-content-end">
                                    @if($booking->status == 'pending')
                                        <button class="btn btn--primary btn-outline-primary font-bold" type="button"
                                                data-toggle="modal" data-target="#editBookingModal">
                                            <i class="tio-edit mr-sm-1"></i> {{translate('messages.Edit_Booking')}}
                                        </button>
                                    @endif
                                    <a class="btn btn--primary print--btn font-bold d-none d-sm-block" href="{{route('admin.beautybooking.booking.generate-invoice',["id" => $booking->id])}}">
                                        <i class="tio-print mr-sm-1"></i> <span>{{translate('messages.Print_Invoice')}}</span>
                                    </a>
                                </div>
                                <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                    <h6>
                                        @php
                                        $statusClasses = [
                                            'pending' => 'badge--pending-1',
                                            'confirmed' => 'badge--accepted',
                                            'completed' => 'badge--accepted',
                                            'cancelled' => 'badge--cancel',
                                            'no_show' => 'badge--cancel',
                                        ];
                                        $badgeClass = $statusClasses[$booking->status] ?? 'badge--accepted';
                                        @endphp
                                        <span>{{translate('messages.Booking_Status')}}</span> <span>:</span>
                                        <span class="{{ $badgeClass }} badge  ml-2 ml-sm-3 text-capitalize">
                                            {{ translate($booking->status) }}
                                        </span>
                                    </h6>
                                    <h6>
                                        <span>{{translate('messages.Payment_status')}}</span> <span>:</span>
                                        <strong class="{{ $booking->payment_status  == 'paid' ? 'text-success' :'text-danger' }}">{{ translate($booking->payment_status) }}</strong>
                                    </h6>
                                    @if ($booking->payment_method)
                                    <h6>
                                        <span>{{translate('messages.Payment_method')}}</span> <span>:</span>
                                        <span class="font-semibold">{{ translate($booking->payment_method ?? 'cash_payment') }}</span>
                                    </h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(!empty($booking->special_requests))
                            <div class="__bg-FAFAFA p-2 rounded">
                                <h6 class="fs-14 text-title">
                                    {{translate('messages.Special_Requests')}}:
                                    <span class="font-regular">{{ $booking->special_requests }}</span>
                                </h6>
                            </div>
                        @endif
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body px-0">
                        <!-- Service Details -->
                        <div class="table-responsive">
                            <table class="table table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th class="border-0">#</th>
                                    <th class="border-0">{{translate('messages.Service_Details')}}</th>
                                    <th class="border-0">{{translate('messages.Staff')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.Duration')}}</th>
                                    <th class="border-0 text-center">{{translate('messages.Price')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <div>1</div>
                                    </td>
                                    <td>
                                        <div class="media media--sm">
                                            <a class="avatar avatar-xl mr-3" href="javascript:">
                                                <img class="img-fluid rounded aspect-ratio-1 onerror-image"
                                                     src="{{ $booking->service->image ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                                     data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                                     alt="Service Image">
                                            </a>
                                            <div class="media-body">
                                                <div class="fs-12 text--title">
                                                    <div class="fz-12 font-semibold line--limit-1">
                                                        {{ $booking->service->name ?? 'N/A' }}
                                                    </div>
                                                    @if ($booking->service)
                                                    <div><span class="font-semibold mr-2">{{translate('messages.Category')}} :</span>{{ Str::limit($booking->service->category->name ?? 'N/A', 15, '...') }}</div>
                                                    <div><span class="font-semibold mr-2">{{translate('messages.Description')}} :</span>{{ Str::limit($booking->service->description ?? 'N/A', 30, '...') }}</div>
                                                    @else
                                                    <div><span class="text--danger mr-2">{{translate('messages.Service_Not_Found')}}</span></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fs-14 text--title">
                                            {{ $booking->staff->name ?? translate('messages.Not_Assigned') }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fs-14 text--title">
                                            {{ $booking->service->duration_minutes ?? 0 }} {{ translate('messages.minutes') }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fs-14 text--title">
                                            {{ \App\CentralLogics\Helpers::format_currency($booking->service->price ?? 0) }}
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mx-3">
                            <hr>
                        </div>
                        <div class="row justify-content-md-end mb-3 mt-4 mx-0">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-right text-title">
                                    <dt class="col-6 font-regular">{{translate('messages.Service_Price')}}</dt>
                                    <dd class="col-6">
                                        {{ \App\CentralLogics\Helpers::format_currency($booking->base_price ?? $booking->service->price ?? 0) }}
                                    </dd>

                                    <dt class="col-6 font-regular">{{translate('messages.Service_Fee')}}</dt>
                                    <dd class="col-6">
                                        +{{ \App\CentralLogics\Helpers::format_currency($booking->service_fee ?? 0) }}
                                    </dd>

                                    @if ($booking->tax_amount > 0)
                                    <dt class="col-6 font-regular text-uppercase">{{translate('messages.Vat_tax')}}</dt>
                                    <dd class="col-6 text-right">
                                        +{{ \App\CentralLogics\Helpers::format_currency($booking->tax_amount ?? 0) }}
                                    </dd>
                                    @endif

                                    @if ($booking->discount_amount > 0)
                                    <dt class="col-6 font-regular">{{translate('messages.Discount')}}</dt>
                                    <dd class="col-6">
                                        -{{ \App\CentralLogics\Helpers::format_currency($booking->discount_amount ?? 0) }}
                                    </dd>
                                    @endif

                                    @if ($booking->cancellation_fee > 0)
                                    <dt class="col-6 font-regular text-danger">{{translate('messages.Cancellation_Fee')}}</dt>
                                    <dd class="col-6 text-danger">
                                        +{{ \App\CentralLogics\Helpers::format_currency($booking->cancellation_fee ?? 0) }}
                                    </dd>
                                    @endif

                                    <dt class="col-6 font-semibold">{{ translate('messages.Total_Amount') }}</dt>
                                    <dd class="col-6 font-semibold">
                                        {{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}
                                    </dd>

                                    <dt class="col-6 font-regular">{{translate('messages.Commission')}}</dt>
                                    <dd class="col-6 text-danger">
                                        -{{ \App\CentralLogics\Helpers::format_currency($booking->commission_amount ?? 0) }}
                                    </dd>

                                    <dt class="col-6 font-semibold">{{ translate('messages.Salon_Payout') }}</dt>
                                    <dd class="col-6 font-semibold text-success">
                                        {{ \App\CentralLogics\Helpers::format_currency($booking->total_amount - ($booking->commission_amount ?? 0)) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->

                @if($booking->cancellation_reason)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Cancellation_Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>{{ translate('messages.Cancelled_By') }}:</strong>
                                <p>{{ ucfirst($booking->cancelled_by ?? 'N/A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>{{ translate('messages.Cancellation_Date') }}:</strong>
                                <p>{{ $booking->cancelled_at ? \App\CentralLogics\Helpers::time_date_format($booking->cancelled_at) : 'N/A' }}</p>
                            </div>
                            <div class="col-md-12">
                                <strong>{{ translate('messages.Reason') }}:</strong>
                                <p>{{ $booking->cancellation_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4 order-print-area-right">
                <!-- Customer Info Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Customer_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center mb-3">
                            <div class="avatar avatar-lg mr-3">
                                <img class="img-fluid rounded-circle" 
                                     src="{{ $booking->user->image ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                     alt="Customer">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '') }}</h6>
                                <span class="text-muted small">{{ $booking->user->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('messages.Email') }}:</span>
                                <span class="text--title">{{ $booking->user->email ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('messages.Total_Bookings') }}:</span>
                                <span class="text--title">{{ $booking->user->beautyBookings->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salon Info Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center mb-3">
                            <div class="avatar avatar-lg mr-3">
                                <img class="img-fluid rounded-circle" 
                                     src="{{ $booking->salon->store->logo ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                     alt="Salon">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ $booking->salon->store->name ?? 'N/A' }}</h6>
                                <span class="text-muted small">{{ $booking->salon->store->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('messages.Phone') }}:</span>
                                <span class="text--title">{{ $booking->salon->store->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ translate('messages.Rating') }}:</span>
                                <span class="text--title">{{ number_format($booking->salon->avg_rating ?? 0, 1) }} ⭐</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.beautybooking.salon.view', $booking->salon_id) }}" class="btn btn--primary btn-block mt-3">
                            <i class="tio-visible"></i> {{ translate('messages.View_Salon') }}
                        </a>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($booking->status != 'cancelled' && $booking->status != 'completed')
                        <form action="{{ route('admin.beautybooking.booking.force-cancel', $booking->id) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label>{{ translate('messages.Cancellation_Reason') }}</label>
                                <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block" data-confirm="{{ translate('Are you sure you want to force cancel this booking? This action cannot be undone.') }}" data-confirm-title="{{ translate('messages.Force_Cancel') }}" data-confirm-modal="true">
                                <i class="tio-clear"></i> {{ translate('messages.Force_Cancel') }}
                            </button>
                        </form>
                        @endif

                        @if($booking->payment_status != 'paid' && $booking->status == 'cancelled')
                        <form action="{{ route('admin.beautybooking.booking.refund', $booking->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>{{ translate('messages.Refund_Amount') }}</label>
                                <input type="number" name="refund_amount" class="form-control" step="0.01" value="{{ $booking->total_amount }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ translate('messages.Refund_Reason') }}</label>
                                <textarea name="refund_reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="tio-money"></i> {{ translate('messages.Process_Refund') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-confirmation.js') }}"></script>
@endpush
