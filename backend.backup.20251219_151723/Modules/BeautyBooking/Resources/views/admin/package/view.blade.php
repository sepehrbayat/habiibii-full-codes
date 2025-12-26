@extends('layouts.admin.app')

@section('title', translate('Package Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('/public/assets/admin/img/items.png') }}" class="w--20" alt="">
                        </span>
                        <span>
                            {{ translate('messages.Package_Details') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Package Details Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Package_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Name') }}:</strong>
                                    <p class="mt-1">{{ $package->name }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Salon') }}:</strong>
                                    <p class="mt-1">
                                        <a href="{{ route('admin.beautybooking.salon.view', $package->salon_id) }}">
                                            {{ $package->salon->store->name ?? 'N/A' }}
                                        </a>
                                    </p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Service') }}:</strong>
                                    <p class="mt-1">{{ $package->service->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Sessions_Count') }}:</strong>
                                    <p class="mt-1">{{ $package->sessions_count }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Total_Price') }}:</strong>
                                    <p class="mt-1">{{ \App\CentralLogics\Helpers::format_currency($package->total_price) }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Discount_Percentage') }}:</strong>
                                    <p class="mt-1">{{ number_format($package->discount_percentage, 1) }}%</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Status') }}:</strong>
                                    <span class="badge badge-{{ $package->status ? 'success' : 'danger' }} ml-2">
                                        {{ $package->status ? translate('messages.Active') : translate('messages.Inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($package->description)
                        <hr>
                        <div class="mb-3">
                            <strong>{{ translate('messages.Description') }}:</strong>
                            <p class="mt-2">{{ $package->description }}</p>
                        </div>
                        @endif

                        @if($package->validity_days)
                        <div class="mb-2">
                            <strong>{{ translate('messages.Validity_Days') }}:</strong>
                            <p class="mt-1">{{ $package->validity_days }} {{ translate('messages.days') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Usage History Card -->
                @if($package->usages && $package->usages->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Usage_History') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('messages.User') }}</th>
                                        <th class="border-0">{{ translate('messages.Used_At') }}</th>
                                        <th class="border-0">{{ translate('messages.Booking_Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->usages as $usage)
                                    <tr>
                                        <td>{{ ($usage->user->f_name ?? '') . ' ' . ($usage->user->l_name ?? '') }}</td>
                                        <td>{{ \App\CentralLogics\Helpers::time_date_format($usage->used_at) }}</td>
                                        <td>
                                            @if($usage->booking)
                                            <a href="{{ route('admin.beautybooking.booking.view', $usage->booking_id) }}">
                                                {{ $usage->booking->booking_reference }}
                                            </a>
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Salon Information Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center mb-3">
                            <div class="avatar avatar-lg mr-3">
                                <img class="img-fluid rounded-circle" 
                                     src="{{ $package->salon->store->logo ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                     alt="Salon">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ $package->salon->store->name ?? 'N/A' }}</h6>
                                <span class="text-muted small">{{ $package->salon->store->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.beautybooking.salon.view', $package->salon_id) }}" class="btn btn--primary btn-block">
                            <i class="tio-visible"></i> {{ translate('messages.View_Salon') }}
                        </a>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ translate('messages.Total_Purchases') }}:</span>
                            <span class="text--title">{{ $package->usages->count() ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ translate('messages.Remaining_Sessions') }}:</span>
                            <span class="text--title">{{ ($package->sessions_count ?? 0) - ($package->usages->count() ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

