@extends('layouts.app')

@section('title', translate('My Dashboard'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('My Dashboard') }}</h1>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3>{{ $upcomingBookings->count() }}</h3>
                    <p class="text-muted mb-0">{{ translate('Upcoming Bookings') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Bookings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('Upcoming Bookings') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Reference') }}</th>
                            <th>{{ translate('Salon') }}</th>
                            <th>{{ translate('Service') }}</th>
                            <th>{{ translate('Date & Time') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingBookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_reference }}</td>
                                <td>{{ $booking->salon->store->name ?? '' }}</td>
                                <td>{{ $booking->service->name ?? '' }}</td>
                                <td>{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('beauty-booking.my-bookings.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                        {{ translate('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ translate('No upcoming bookings') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-3">
        <div class="col-md-3">
            <a href="{{ route('beauty-booking.my-bookings.index') }}" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="tio-calendar-note fs-1 text-primary"></i>
                    <p class="mb-0 mt-2">{{ translate('My Bookings') }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('beauty-booking.wallet') }}" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="tio-wallet fs-1 text-success"></i>
                    <p class="mb-0 mt-2">{{ translate('Wallet') }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('beauty-booking.gift-cards') }}" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="tio-gift fs-1 text-info"></i>
                    <p class="mb-0 mt-2">{{ translate('Gift Cards') }}</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('beauty-booking.loyalty') }}" class="card text-center text-decoration-none">
                <div class="card-body">
                    <i class="tio-star fs-1 text-warning"></i>
                    <p class="mb-0 mt-2">{{ translate('Loyalty Points') }}</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

