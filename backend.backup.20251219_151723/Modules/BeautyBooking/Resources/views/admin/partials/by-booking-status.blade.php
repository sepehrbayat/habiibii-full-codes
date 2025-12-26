<div class="position-relative pie-chart">
    <div id="dognut-pie"></div>
    <!-- Total Orders -->
    <div class="total--orders">
        <h3 class="text-uppercase mb-xxl-2">
            {{ $totalCount }}</h3>
        <span class="text-capitalize">{{ translate('messages.total_bookings') }}</span>
    </div>
    <!-- Total Orders -->
</div>
<div class="d-flex flex-wrap justify-content-center mt-4">
    <div class="chart--label">
        <span class="indicator chart-bg-1"></span>
        <span class="info">
            {{ translate('messages.pending') }} {{ $pendingCount }}
        </span>
    </div>
    <div class="chart--label">
        <span class="indicator chart-bg-2"></span>
        <span class="info">
            {{ translate('messages.confirmed') }} {{ $confirmedCount }}
        </span>
    </div>
    <div class="chart--label">
        <span class="indicator chart-bg-3"></span>
        <span class="info">
            {{ translate('messages.completed') }} {{ $completedCount }}
        </span>
    </div>
    <div class="chart--label">
        <span class="indicator chart-bg-4"></span>
        <span class="info">
            {{ translate('messages.cancelled') }} {{ $cancelledCount }}
        </span>
    </div>
</div>
