@if(isset($beautyCharts) && !empty($beautyCharts))
<!-- Charts Section -->
<!-- بخش نمودارها -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ translate('Beauty Booking Analytics') }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- Booking Trends Chart -->
            <!-- نمودار روند رزروها -->
            @if(isset($beautyCharts['booking_trends']) && !empty($beautyCharts['booking_trends']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ translate('Booking Trends') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="booking-trends-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Spending Trends Chart -->
            <!-- نمودار روند هزینه‌ها -->
            @if(isset($beautyCharts['spending_trends']) && !empty($beautyCharts['spending_trends']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ translate('Spending Trends') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="spending-trends-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Most Used Services Chart -->
            <!-- نمودار بیشترین خدمات استفاده شده -->
            @if(isset($beautyCharts['most_used_services']) && !empty($beautyCharts['most_used_services']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ translate('Most Used Services') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="most-used-services-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Favorite Salons Chart -->
            <!-- نمودار سالن‌های مورد علاقه -->
            @if(isset($beautyCharts['favorite_salons']) && !empty($beautyCharts['favorite_salons']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ translate('Favorite Salons') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="favorite-salons-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Loyalty Points Trend -->
            <!-- روند امتیازات وفاداری -->
            @if(isset($beautyCharts['loyalty_trends']) && !empty($beautyCharts['loyalty_trends']))
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ translate('Loyalty Points Earned Over Time') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="loyalty-trends-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('script_2')
<script>
    "use strict";
    
    function initializeBookingTrendsChart() {
        @if(isset($beautyCharts['booking_trends']))
            const bookingTrendsData = @json($beautyCharts['booking_trends']);
            const options = {
                series: [{
                    name: '{{ translate('Bookings') }}',
                    data: bookingTrendsData.map(item => item.count)
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: bookingTrendsData.map(item => item.month)
                },
                colors: ['#007bff']
            };
            
            const chart = new ApexCharts(document.querySelector("#booking-trends-chart"), options);
            chart.render();
        @endif
    }
    
    function initializeSpendingTrendsChart() {
        @if(isset($beautyCharts['spending_trends']))
            const spendingTrendsData = @json($beautyCharts['spending_trends']);
            const options = {
                series: [{
                    name: '{{ translate('Spending') }}',
                    data: spendingTrendsData.map(item => parseFloat(item.amount))
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3
                    }
                },
                xaxis: {
                    categories: spendingTrendsData.map(item => item.month)
                },
                colors: ['#28a745']
            };
            
            const chart = new ApexCharts(document.querySelector("#spending-trends-chart"), options);
            chart.render();
        @endif
    }
    
    function initializeMostUsedServicesChart() {
        @if(isset($beautyCharts['most_used_services']))
            const servicesData = @json($beautyCharts['most_used_services']);
            const options = {
                series: [{
                    name: '{{ translate('Bookings') }}',
                    data: servicesData.map(item => item.count)
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: { show: false }
                },
                xaxis: {
                    categories: servicesData.map(item => item.service_name)
                },
                colors: ['#17a2b8']
            };
            
            const chart = new ApexCharts(document.querySelector("#most-used-services-chart"), options);
            chart.render();
        @endif
    }
    
    function initializeFavoriteSalonsChart() {
        @if(isset($beautyCharts['favorite_salons']))
            const salonsData = @json($beautyCharts['favorite_salons']);
            const options = {
                series: [{
                    name: '{{ translate('Bookings') }}',
                    data: salonsData.map(item => item.count)
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: { show: false }
                },
                xaxis: {
                    categories: salonsData.map(item => item.salon_name)
                },
                colors: ['#ffc107']
            };
            
            const chart = new ApexCharts(document.querySelector("#favorite-salons-chart"), options);
            chart.render();
        @endif
    }
    
    function initializeLoyaltyTrendsChart() {
        @if(isset($beautyCharts['loyalty_trends']))
            const loyaltyTrendsData = @json($beautyCharts['loyalty_trends']);
            const options = {
                series: [{
                    name: '{{ translate('Points Earned') }}',
                    data: loyaltyTrendsData.map(item => item.points)
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: loyaltyTrendsData.map(item => item.month)
                },
                colors: ['#ffc107']
            };
            
            const chart = new ApexCharts(document.querySelector("#loyalty-trends-chart"), options);
            chart.render();
        @endif
    }
</script>
@endpush
@endif

