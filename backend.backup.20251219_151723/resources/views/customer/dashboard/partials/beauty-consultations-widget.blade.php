@if(isset($beautyWidgets['upcoming_bookings']))
<!-- Consultations Widget -->
<!-- ویجت مشاوره‌ها -->
@php
    $consultations = $beautyWidgets['upcoming_bookings']->filter(function($booking) {
        return isset($booking->service) && 
               isset($booking->service->service_type) && 
               in_array($booking->service->service_type, ['pre_consultation', 'post_consultation']);
    });
@endphp
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Consultations') }}</h5>
        <a href="{{ route('beauty-booking.consultations') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        @if($consultations->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($consultations->take(5) as $consultation)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $consultation->service->name ?? translate('Consultation') }}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    {{ $consultation->salon->store->name ?? translate('Unknown Salon') }}
                                </p>
                                <p class="mb-0 small">
                                    <i class="tio-calendar"></i> {{ $consultation->booking_date ? $consultation->booking_date->format('Y-m-d') : 'N/A' }}
                                    <i class="tio-time ml-2"></i> {{ $consultation->booking_time ?? 'N/A' }}
                                </p>
                                <span class="badge badge-{{ ($consultation->service->service_type ?? '') == 'pre_consultation' ? 'info' : 'primary' }}">
                                    {{ ($consultation->service->service_type ?? '') == 'pre_consultation' ? translate('Pre-Consultation') : translate('Post-Consultation') }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <a href="{{ route('beauty-booking.my-bookings.show', $consultation->id ?? 0) }}" class="btn btn-sm btn-primary">
                                    {{ translate('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="tio-chat fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No active consultations') }}</p>
                <a href="{{ route('beauty-booking.booking.create', 0) }}" class="btn btn-sm btn-primary">
                    {{ translate('Book Consultation') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

