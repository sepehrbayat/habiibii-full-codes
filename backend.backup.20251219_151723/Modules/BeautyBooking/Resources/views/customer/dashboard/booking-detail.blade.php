@extends('layouts.app')

@section('title', translate('Booking Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.APP_URL = '{{ url('/') }}';
        window.translate = function(key) {
            const translations = {
                'Write a Review': '{{ translate('Write a Review') }}',
                'Rating': '{{ translate('Rating') }}',
                'Comment': '{{ translate('Comment') }}',
                'Share your experience...': '{{ translate('Share your experience...') }}',
                'Attachments (Optional)': '{{ translate('Attachments (Optional)') }}',
                'file(s) selected': '{{ translate('file(s) selected') }}',
                'Cancel': '{{ translate('Cancel') }}',
                'Submitting...': '{{ translate('Submitting...') }}',
                'Submit Review': '{{ translate('Submit Review') }}',
                'Please provide a rating': '{{ translate('Please provide a rating') }}',
            };
            return translations[key] || key;
        };
        window.bookingData = {
            id: {{ $booking->id }},
            salon_id: {{ $booking->salon_id }},
            service_id: {{ $booking->service_id ?? 'null' }},
            status: '{{ $booking->status }}',
            has_review: {{ $booking->review ? 'true' : 'false' }},
        };
    </script>
    <link rel="stylesheet" href="{{ mix('css/beauty-booking.css', 'public') }}">
@endpush

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('Booking Details') }} - {{ $booking->booking_reference }}</h1>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Booking Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>{{ translate('Booking Reference') }}:</strong>
                            <p>{{ $booking->booking_reference }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Status') }}:</strong>
                            <p>
                                <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Salon') }}:</strong>
                            <p>{{ $booking->salon->store->name ?? '' }}</p>
                            <p class="text-muted small">{{ $booking->salon->store->address ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Service') }}:</strong>
                            <p>{{ $booking->service->name ?? '' }}</p>
                            <p class="text-muted small">{{ $booking->service->duration_minutes ?? '' }} {{ translate('minutes') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Staff') }}:</strong>
                            <p>{{ $booking->staff->name ?? translate('Not Assigned') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Date & Time') }}:</strong>
                            <p>{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($booking->canCancel())
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('api.beautybooking.bookings.cancel', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>{{ translate('Cancellation Reason') }}</label>
                            <textarea name="cancellation_reason" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" data-confirm="{{ translate('Are you sure you want to cancel this booking?') }}" data-confirm-title="{{ translate('Cancel Booking') }}">
                            {{ translate('Cancel Booking') }}
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Financial Details') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>{{ translate('Total Amount') }}:</td>
                            <td class="text-right"><strong>{{ number_format($booking->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>{{ translate('Payment Status') }}:</td>
                            <td class="text-right">
                                <span class="badge badge-{{ $booking->payment_status == 'paid' ? 'success' : 'danger' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($booking->conversation_id)
            <div class="card mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('customer.message.view', ['conversation' => $booking->conversation_id]) }}" class="btn btn-secondary">
                        <i class="tio-chat"></i> {{ translate('Chat with Salon') }}
                    </a>
                </div>
            </div>
            @endif

            @if($booking->status == 'completed' && !$booking->review)
            <div class="card mt-3">
                <div class="card-body text-center">
                    <!-- React Review Modal Button -->
                    <!-- دکمه مودال نظر React -->
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="open-booking-review-modal-btn"
                        data-booking-id="{{ $booking->id }}"
                        data-salon-id="{{ $booking->salon_id }}"
                        data-service-id="{{ $booking->service_id }}"
                    >
                        <i class="tio-star"></i> {{ translate('Write Review') }}
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-confirmation.js') }}"></script>
@endsection

@push('script')
    <script src="{{ mix('js/beauty-booking.js', 'public') }}" defer></script>
@endpush

