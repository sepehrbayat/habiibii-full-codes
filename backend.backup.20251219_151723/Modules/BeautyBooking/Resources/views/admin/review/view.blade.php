@extends('layouts.admin.app')

@section('title', translate('Review Details'))

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
                            {{ translate('messages.Review_Details') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Review Details Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Review_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-lg mr-3">
                                        <img class="img-fluid rounded-circle" 
                                             src="{{ $review->user->image ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                             alt="Customer">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ ($review->user->f_name ?? '') . ' ' . ($review->user->l_name ?? '') }}</h6>
                                        <span class="text-muted small">{{ $review->user->phone ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Rating') }}:</strong>
                                    <div class="mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="tio-star{{ $i <= $review->rating ? '' : '-outlined' }} text-warning fs-18"></i>
                                        @endfor
                                        <span class="ml-2">({{ $review->rating }}/5)</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Status') }}:</strong>
                                    <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }} ml-2">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Date') }}:</strong>
                                    <span class="ml-2">{{ \App\CentralLogics\Helpers::time_date_format($review->created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <strong>{{ translate('messages.Comment') }}:</strong>
                            <p class="mt-2">{{ $review->comment ?? translate('messages.No_Comment') }}</p>
                        </div>

                        @if($review->attachments && count($review->attachments) > 0)
                        <div class="mb-3">
                            <strong>{{ translate('messages.Attachments') }}:</strong>
                            <div class="row g-2 mt-2">
                                @foreach($review->attachments as $attachment)
                                    <div class="col-md-3">
                                        <a href="{{ asset('storage/app/public/' . $attachment) }}" target="_blank">
                                            <img src="{{ asset('storage/app/public/' . $attachment) }}" 
                                                 class="img-thumbnail" 
                                                 alt="Attachment" 
                                                 style="max-height: 100px;">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($review->admin_notes)
                        <div class="mb-3">
                            <strong>{{ translate('messages.Admin_Notes') }}:</strong>
                            <p class="mt-2 text-muted">{{ $review->admin_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Booking Information Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Booking_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Booking_Reference') }}:</strong>
                                    <span class="ml-2">
                                        <a href="{{ route('admin.beautybooking.booking.view', $review->booking_id) }}">
                                            {{ $review->booking->booking_reference ?? 'N/A' }}
                                        </a>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Booking_Date') }}:</strong>
                                    <span class="ml-2">
                                        {{ $review->booking?->booking_date?->format('Y-m-d') ?? 'N/A' }} 
                                        {{ $review->booking?->booking_time ?? '' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Service') }}:</strong>
                                    <span class="ml-2">{{ $review->service->name ?? 'N/A' }}</span>
                                </div>
                                @if($review->staff)
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Staff') }}:</strong>
                                    <span class="ml-2">{{ $review->staff->name ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
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
                                     src="{{ $review->salon->store->logo ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                     alt="Salon">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ $review->salon->store->name ?? 'N/A' }}</h6>
                                <span class="text-muted small">{{ $review->salon->store->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.beautybooking.salon.view', $review->salon_id) }}" class="btn btn--primary btn-block">
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
                        @if($review->status == 'pending')
                        <form action="{{ route('admin.beautybooking.review.approve', $review->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="tio-checkmark"></i> {{ translate('messages.Approve_Review') }}
                            </button>
                        </form>
                        <form action="{{ route('admin.beautybooking.review.reject', $review->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>{{ translate('messages.Rejection_Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="admin_notes" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="tio-close"></i> {{ translate('messages.Reject_Review') }}
                            </button>
                        </form>
                        @else
                        <div class="alert alert-{{ $review->status == 'approved' ? 'success' : 'danger' }}">
                            <strong>{{ translate('messages.Review_Status') }}:</strong> 
                            {{ ucfirst($review->status) }}
                        </div>
                        @if($review->admin_notes)
                        <div class="mt-3">
                            <strong>{{ translate('messages.Admin_Notes') }}:</strong>
                            <p class="mt-2">{{ $review->admin_notes }}</p>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

