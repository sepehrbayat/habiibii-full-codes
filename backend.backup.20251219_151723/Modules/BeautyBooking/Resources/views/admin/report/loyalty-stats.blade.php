@extends('layouts.admin.app')

@section('title', translate('Loyalty Campaign Statistics'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{asset('/public/assets/admin/img/report/report.png')}}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Loyalty_Campaign_Performance') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <div class="card-header">
                <form method="get">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('From Date') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('To Date') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn--primary btn-block text-center">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['points_earned']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Points Earned') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['points_redeemed']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Points Redeemed') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['active_campaigns']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Active Campaigns') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['conversion_rate'], 1) }}%</h3>
                        <p class="text-muted mb-0">{{ translate('Points Conversion Rate') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Points Trend') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="points-trend-chart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Top Campaigns') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('Campaign') }}</th>
                                        <th class="border-0">{{ translate('Salon') }}</th>
                                        <th class="border-0 text-right">{{ translate('Points Earned') }}</th>
                                        <th class="border-0 text-right">{{ translate('Participants') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCampaigns as $campaign)
                                        <tr>
                                            <td><span class="text--title">{{ $campaign->name }}</span></td>
                                            <td><span class="text--title">{{ $campaign->salon->store->name ?? translate('Platform-wide') }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ number_format($campaign->points_earned ?? 0) }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ number_format($campaign->total_participants) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <div class="empty--data">
                                                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                                                    <h5>{{ translate('No campaign data available for selected period') }}</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <!-- Apex Charts -->
    <script src="{{ asset('/public/assets/admin/js/apex-charts/apexcharts.js') }}"></script>
    <!-- Apex Charts -->
@endpush

@push('script_2')
<script>
    "use strict";

    @if(isset($pointsTrend) && !empty($pointsTrend['labels']) && !empty($pointsTrend['data']))
    // Get chart data
    // دریافت داده‌های نمودار
    const chartLabels = @json($pointsTrend['labels']);
    const chartData = @json($pointsTrend['data']);
    
    // Ensure data is numeric
    // اطمینان از عددی بودن داده‌ها
    const seriesData = chartData.map(value => parseFloat(value) || 0);

    const pointsTrendOptions = {
        chart: {
            height: 300,
            type: 'area',
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        series: [
            {
                name: '{{ translate('Net Points') }}',
                data: seriesData
            }
        ],
        labels: chartLabels,
        colors: ['#00aa96'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            type: 'category'
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString('en-US');
                }
            }
        }
    };

    // Initialize chart after DOM and scripts are loaded
    // راه‌اندازی نمودار پس از بارگذاری DOM و اسکریپت‌ها
    function initPointsTrendChart() {
        const chartElement = document.querySelector("#points-trend-chart");
        if (!chartElement) {
            console.error('Points trend chart container not found');
            return;
        }
        
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library is not loaded');
            chartElement.innerHTML = '<div class="text-center p-4"><p class="text-danger">Chart library failed to load. Please refresh the page.</p></div>';
            return;
        }
        
        try {
            // Destroy existing chart if any
            // از بین بردن نمودار موجود در صورت وجود
            if (window.pointsTrendChartInstance) {
                window.pointsTrendChartInstance.destroy();
            }
            
            window.pointsTrendChartInstance = new ApexCharts(chartElement, pointsTrendOptions);
            window.pointsTrendChartInstance.render();
        } catch (error) {
            console.error('Error rendering points trend chart:', error);
            chartElement.innerHTML = '<div class="text-center p-4"><p class="text-danger">Error rendering chart: ' + error.message + '</p></div>';
        }
    }
    
    // Wait for DOM and ApexCharts to be ready
    // منتظر ماندن برای آماده بودن DOM و ApexCharts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for ApexCharts script to load
            // کمی صبر برای بارگذاری اسکریپت ApexCharts
            setTimeout(initPointsTrendChart, 100);
        });
    } else {
        // DOM already loaded
        setTimeout(initPointsTrendChart, 100);
    }
    @else
    // No data to display
    // هیچ داده‌ای برای نمایش وجود ندارد
    document.addEventListener('DOMContentLoaded', function() {
        const chartContainer = document.querySelector("#points-trend-chart");
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="text-center p-4"><p class="text-muted">{{ translate("No points trend data available for the selected period") }}</p></div>';
        }
    });
    @endif
</script>
@endpush
