@if(isset($beautyWidgets['recent_loyalty_points']) && isset($beautyStats['loyalty_points_balance']))
<!-- Loyalty Program Widget -->
<!-- ویجت برنامه وفاداری -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Loyalty Points') }}</h5>
        <a href="{{ route('beauty-booking.loyalty') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        <!-- Current Balance -->
        <!-- موجودی فعلی -->
        <div class="text-center mb-4 pb-3 border-bottom">
            <h2 class="text-warning mb-1">{{ number_format($beautyStats['loyalty_points_balance']) }}</h2>
            <p class="text-muted mb-0">{{ translate('Current Points Balance') }}</p>
        </div>
        
        <!-- Recent Transactions -->
        <!-- تراکنش‌های اخیر -->
        <h6 class="mb-3">{{ translate('Recent Transactions') }}</h6>
        @if($beautyWidgets['recent_loyalty_points']->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['recent_loyalty_points']->take(5) as $point)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1">
                                    <strong class="{{ $point->points > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $point->points > 0 ? '+' : '' }}{{ $point->points }} {{ translate('points') }}
                                    </strong>
                                </p>
                                <p class="mb-0 text-muted small">
                                    {{ $point->description ?? translate('Loyalty points transaction') }}
                                </p>
                                @if($point->booking)
                                    <p class="mb-0 text-muted small">
                                        {{ translate('Booking') }} #{{ $point->booking->booking_reference }}
                                    </p>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $point->created_at ? $point->created_at->format('M d, Y') : 'N/A' }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3">
                <p class="text-muted">{{ translate('No recent transactions') }}</p>
            </div>
        @endif
        
        <!-- Expiration Warning -->
        <!-- هشدار انقضا -->
        @php
            // Use conditional class loading to prevent ClassNotFoundException
            // استفاده از بارگذاری شرطی کلاس برای جلوگیری از ClassNotFoundException
            $loyaltyPointClass = 'Modules\BeautyBooking\Entities\BeautyLoyaltyPoint';
            $expiringPoints = 0;
            if (class_exists($loyaltyPointClass, true)) {
                $expiringPoints = $loyaltyPointClass::where('user_id', auth('customer')->id())
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->sum('points');
            }
        @endphp
        @if($expiringPoints > 0)
            <div class="alert alert-warning mt-3 mb-0">
                <i class="tio-warning"></i> 
                {{ translate('You have') }} {{ number_format($expiringPoints) }} {{ translate('points expiring within 30 days') }}
            </div>
        @endif
    </div>
</div>
@endif

