@if(isset($beautyWidgets['pending_reviews']))
<!-- Reviews Widget -->
<!-- ویجت نظرات -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Reviews') }}</h5>
        <a href="{{ route('beauty-booking.reviews') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        @if($beautyWidgets['pending_reviews']->count() > 0)
            <div class="alert alert-info mb-3">
                <i class="tio-info"></i> 
                {{ translate('You have') }} {{ $beautyWidgets['pending_reviews']->count() }} {{ translate('completed bookings waiting for your review') }}
            </div>
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['pending_reviews']->take(3) as $booking)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $booking->salon->store->name ?? translate('Unknown Salon') }}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    {{ $booking->service->name ?? translate('Unknown Service') }}
                                </p>
                                <p class="mb-0 text-muted small">
                                    {{ translate('Completed on') }} {{ $booking->updated_at ? $booking->updated_at->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <a href="{{ route('beauty-booking.reviews') }}" class="btn btn-sm btn-primary">
                                {{ translate('Review') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($beautyWidgets['pending_reviews']->count() > 3)
                <div class="text-center mt-3">
                    <a href="{{ route('beauty-booking.reviews') }}" class="btn btn-sm btn-outline-primary">
                        {{ translate('View All Pending Reviews') }} ({{ $beautyWidgets['pending_reviews']->count() }})
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="tio-comment fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No pending reviews') }}</p>
                <a href="{{ route('beauty-booking.reviews') }}" class="btn btn-sm btn-primary">
                    {{ translate('View My Reviews') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

