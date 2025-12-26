@extends('layouts.app')

@section('title', translate('My Bookings'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('My Bookings') }}</h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="status" class="form-control">
                            <option value="">{{ translate('All Status') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ translate('Pending') }}</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ translate('Confirmed') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ translate('Completed') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ translate('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Reference') }}</th>
                            <th>{{ translate('Salon') }}</th>
                            <th>{{ translate('Service') }}</th>
                            <th>{{ translate('Date & Time') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_reference }}</td>
                                <td>{{ $booking->salon->store->name ?? '' }}</td>
                                <td>{{ $booking->service->name ?? '' }}</td>
                                <td>{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</td>
                                <td>{{ number_format($booking->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('beauty-booking.my-bookings.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                        {{ translate('View') }}
                                    </a>
                                    @if($booking->canCancel())
                                        <form action="{{ route('api.beautybooking.bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="{{ translate('Are you sure you want to cancel this booking?') }}" data-confirm-title="{{ translate('Cancel Booking') }}">
                                                {{ translate('Cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ translate('No bookings found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        </div>
    </div>
</div>

<script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-confirmation.js') }}"></script>
@endsection

