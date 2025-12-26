@if(isset($beautyStats) && !empty($beautyStats))
<!-- Quick Stats Cards -->
<!-- کارت‌های آمار سریع -->
<div class="row g-3 mb-4">
    <!-- Upcoming Bookings -->
    <!-- رزروهای آینده -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-calendar-note fs-1 text-primary mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['upcoming_bookings'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Upcoming Bookings') }}</p>
                <a href="{{ route('beauty-booking.my-bookings.index') }}" class="btn btn-sm btn-link mt-2">
                    {{ translate('View All') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Total Bookings -->
    <!-- کل رزروها -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-book fs-1 text-info mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['total_bookings'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Bookings') }}</p>
                <small class="text-muted">
                    {{ translate('This Month') }}: {{ $beautyStats['bookings_this_month'] ?? 0 }} | 
                    {{ translate('This Year') }}: {{ $beautyStats['bookings_this_year'] ?? 0 }}
                </small>
            </div>
        </div>
    </div>
    
    <!-- Total Spent -->
    <!-- کل هزینه شده -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-money fs-1 text-success mb-2"></i>
                <h3 class="mb-1">{{ \App\CentralLogics\Helpers::format_currency($beautyStats['total_spent'] ?? 0) }}</h3>
                <p class="text-muted mb-0">{{ translate('Total Spent') }}</p>
            </div>
        </div>
    </div>
    
    <!-- Active Packages -->
    <!-- پکیج‌های فعال -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-package fs-1 text-warning mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['active_packages'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Active Packages') }}</p>
                <a href="{{ route('beauty-booking.search') }}" class="btn btn-sm btn-link mt-2">
                    {{ translate('Browse Packages') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Gift Card Balance -->
    <!-- موجودی کارت هدیه -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-gift fs-1 text-danger mb-2"></i>
                <h3 class="mb-1">{{ \App\CentralLogics\Helpers::format_currency($beautyStats['gift_card_balance'] ?? 0) }}</h3>
                <p class="text-muted mb-0">{{ translate('Gift Card Balance') }}</p>
                <a href="{{ route('beauty-booking.gift-cards') }}" class="btn btn-sm btn-link mt-2">
                    {{ translate('View Cards') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Loyalty Points -->
    <!-- امتیازات وفاداری -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-star fs-1 text-warning mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['loyalty_points_balance'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Loyalty Points') }}</p>
                <a href="{{ route('beauty-booking.loyalty') }}" class="btn btn-sm btn-link mt-2">
                    {{ translate('View Points') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Pending Reviews -->
    <!-- نظرات در انتظار -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-comment fs-1 text-secondary mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['pending_reviews'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Pending Reviews') }}</p>
                @if(($beautyStats['pending_reviews'] ?? 0) > 0)
                    <a href="{{ route('beauty-booking.reviews') }}" class="btn btn-sm btn-primary mt-2">
                        {{ translate('Submit Review') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Active Consultations -->
    <!-- مشاوره‌های فعال -->
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card text-center">
            <div class="card-body">
                <i class="tio-chat fs-1 text-info mb-2"></i>
                <h3 class="mb-1">{{ $beautyStats['active_consultations'] ?? 0 }}</h3>
                <p class="text-muted mb-0">{{ translate('Active Consultations') }}</p>
                <a href="{{ route('beauty-booking.consultations') }}" class="btn btn-sm btn-link mt-2">
                    {{ translate('View') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endif

