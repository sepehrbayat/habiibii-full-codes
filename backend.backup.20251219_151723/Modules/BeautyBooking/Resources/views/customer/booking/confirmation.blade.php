@extends('layouts.app')

@section('title', translate('Booking Confirmed'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="tio-checkmark-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="mb-3">{{ translate('Booking Confirmed!') }}</h2>
                    <p class="text-muted mb-4">{{ translate('Your booking has been successfully created.') }}</p>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body text-left">
                            <h5 class="mb-3">{{ translate('Booking Details') }}</h5>
                            <div class="row mb-2">
                                <div class="col-4"><strong>{{ translate('Reference') }}:</strong></div>
                                <div class="col-8">{{ $booking->booking_reference }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>{{ translate('Salon') }}:</strong></div>
                                <div class="col-8">{{ $booking->salon->store->name ?? '' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>{{ translate('Service') }}:</strong></div>
                                <div class="col-8">{{ $booking->service->name ?? '' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>{{ translate('Date & Time') }}:</strong></div>
                                <div class="col-8">{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</div>
                            </div>
                            <div class="row">
                                <div class="col-4"><strong>{{ translate('Total Amount') }}:</strong></div>
                                <div class="col-8"><strong>{{ number_format($booking->total_amount, 2) }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('beauty-booking.my-bookings.show', $booking->id) }}" class="btn btn-primary">
                            {{ translate('View Booking') }}
                        </a>
                        <a href="{{ route('beauty-booking.dashboard') }}" class="btn btn-outline-secondary">
                            {{ translate('Go to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

