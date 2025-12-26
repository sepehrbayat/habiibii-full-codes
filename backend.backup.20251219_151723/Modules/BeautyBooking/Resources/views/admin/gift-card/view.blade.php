@extends('layouts.admin.app')

@section('title', translate('Gift Card Details'))

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
                            {{ translate('messages.Gift_Card_Details') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Gift Card Details Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Gift_Card_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Code') }}:</strong>
                                    <p class="mt-1"><code class="fs-18">{{ $giftCard->code }}</code></p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Amount') }}:</strong>
                                    <p class="mt-1 fs-18 text-success">{{ \App\CentralLogics\Helpers::format_currency($giftCard->amount) }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Status') }}:</strong>
                                    <span class="badge badge-{{ $giftCard->status == 'active' ? 'success' : ($giftCard->status == 'redeemed' ? 'info' : 'danger') }} ml-2">
                                        {{ ucfirst($giftCard->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Purchased_At') }}:</strong>
                                    <p class="mt-1">{{ \App\CentralLogics\Helpers::time_date_format($giftCard->created_at) }}</p>
                                </div>
                                @if($giftCard->expires_at)
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Expires_At') }}:</strong>
                                    <p class="mt-1">{{ \App\CentralLogics\Helpers::time_date_format($giftCard->expires_at) }}</p>
                                </div>
                                @endif
                                @if($giftCard->redeemed_at)
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Redeemed_At') }}:</strong>
                                    <p class="mt-1">{{ \App\CentralLogics\Helpers::time_date_format($giftCard->redeemed_at) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Purchased_By') }}:</strong>
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="avatar avatar-sm mr-2">
                                            <img class="img-fluid rounded-circle" 
                                                 src="{{ $giftCard->purchaser->image ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                                 alt="Purchaser">
                                        </div>
                                        <div>
                                            <div>{{ ($giftCard->purchaser->f_name ?? '') . ' ' . ($giftCard->purchaser->l_name ?? '') }}</div>
                                            <small class="text-muted">{{ $giftCard->purchaser->phone ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($giftCard->redeemed_by)
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Redeemed_By') }}:</strong>
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="avatar avatar-sm mr-2">
                                            <img class="img-fluid rounded-circle" 
                                                 src="{{ $giftCard->redeemer->image ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                                 alt="Redeemer">
                                        </div>
                                        <div>
                                            <div>{{ ($giftCard->redeemer->f_name ?? '') . ' ' . ($giftCard->redeemer->l_name ?? '') }}</div>
                                            <small class="text-muted">{{ $giftCard->redeemer->phone ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($giftCard->message)
                        <hr>
                        <div class="mb-3">
                            <strong>{{ translate('messages.Message') }}:</strong>
                            <p class="mt-2">{{ $giftCard->message }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Salon Information Card -->
                @if($giftCard->salon)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center mb-3">
                            <div class="avatar avatar-lg mr-3">
                                <img class="img-fluid rounded-circle" 
                                     src="{{ $giftCard->salon->store->logo ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                     alt="Salon">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ $giftCard->salon->store->name ?? 'N/A' }}</h6>
                                <span class="text-muted small">{{ $giftCard->salon->store->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.beautybooking.salon.view', $giftCard->salon_id) }}" class="btn btn--primary btn-block">
                            <i class="tio-visible"></i> {{ translate('messages.View_Salon') }}
                        </a>
                    </div>
                </div>
                @else
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ translate('messages.General_Gift_Card_Not_Salon_Specific') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

