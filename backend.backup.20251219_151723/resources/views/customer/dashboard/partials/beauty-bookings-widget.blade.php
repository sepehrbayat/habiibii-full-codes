@if(isset($beautyWidgets['upcoming_bookings']))
<!-- Bookings Widget -->
<!-- ویجت رزروها -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Upcoming Bookings') }}</h5>
        <a href="{{ route('beauty-booking.my-bookings.index') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        @if($beautyWidgets['upcoming_bookings']->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['upcoming_bookings']->take(5) as $booking)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $booking->salon->store->name ?? ($booking->salon->name ?? translate('Unknown Salon')) }}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    {{ $booking->service->name ?? translate('Unknown Service') }}
                                </p>
                                <p class="mb-0 small">
                                    <i class="tio-calendar"></i> {{ $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'N/A' }}
                                    <i class="tio-time ml-2"></i> {{ $booking->booking_time ?? 'N/A' }}
                                </p>
                                <span class="badge badge-{{ ($booking->status ?? '') == 'confirmed' ? 'success' : (($booking->status ?? '') == 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($booking->status ?? 'unknown') }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <a href="{{ route('beauty-booking.my-bookings.show', $booking->id ?? 0) }}" class="btn btn-sm btn-primary">
                                    {{ translate('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="tio-calendar-note fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No upcoming bookings') }}</p>
                <a href="{{ route('beauty-booking.booking.create', 0) }}" class="btn btn-sm btn-primary">
                    {{ translate('Book Appointment') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

