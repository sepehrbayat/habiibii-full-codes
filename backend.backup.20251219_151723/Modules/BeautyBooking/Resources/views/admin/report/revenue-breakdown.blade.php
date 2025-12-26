@extends('layouts.admin.app')

@section('title', translate('Revenue Breakdown'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('script')
    <!-- Apex Charts -->
    <script src="{{ asset('/public/assets/admin/js/apex-charts/apexcharts.js') }}"></script>
    <!-- End Apex Charts -->
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
                        <span>{{ translate('messages.Revenue_Breakdown_by_Model') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <div class="card-header">
                <form action="" method="get">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('messages.From_Date') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('messages.To_Date') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn--primary btn-block text-center">{{ translate('messages.filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('Revenue Breakdown Chart') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="revenue-breakdown-chart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('Revenue Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('messages.Revenue_Model') }}</th>
                                        <th class="text-right border-0">{{ translate('messages.Amount') }}</th>
                                        <th class="text-right border-0">{{ translate('messages.Percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total = array_sum($revenueByModel);
                                    @endphp
                                    @foreach($revenueByModel as $type => $amount)
                                        @if($amount > 0)
                                        <tr>
                                            <td><span class="text--title">{{ ucfirst(str_replace('_', ' ', $type)) }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($amount) }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ $total > 0 ? number_format(($amount / $total) * 100, 1) : 0 }}%</span></td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    <tr class="font-weight-bold">
                                        <td><span class="text--title">{{ translate('messages.Total_Revenue') }}</span></td>
                                        <td class="text-right"><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($total) }}</span></td>
                                        <td class="text-right"><span class="text--title">100%</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";
    
    // Prepare chart data - only include non-zero values
    // آماده‌سازی داده‌های نمودار - فقط مقادیر غیر صفر را شامل شود
    @php
        $chartData = [];
        $chartLabels = [];
        $chartColors = ['#005555', '#00aa96', '#b9e0e0', '#ff6d6d', '#76ffcd', '#ffa500', '#9b59b6', '#3498db', '#e74c3c', '#2ecc71', '#f39c12'];
        $colorIndex = 0;
        
        $typeLabels = [
            'commission' => translate('Commission'),
            'subscription' => translate('Subscription'),
            'advertisement' => translate('Advertisement'),
            'service_fee' => translate('Service Fee'),
            'package_sale' => translate('Package Sale'),
            'cancellation_fee' => translate('Cancellation Fee'),
            'consultation_fee' => translate('Consultation'),
            'cross_selling' => translate('Cross Selling'),
            'retail_sale' => translate('Retail'),
            'gift_card_sale' => translate('Gift Card'),
            'loyalty_campaign' => translate('Loyalty')
        ];
        
        foreach ($revenueByModel as $type => $amount) {
            $amount = (float)($amount ?? 0);
            if ($amount > 0) {
                $chartData[] = $amount;
                $chartLabels[] = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
            }
        }
    @endphp
    
    @if(count($chartData) > 0)
    let revenueBreakdownOptions = {
        series: @json($chartData),
        chart: {
            width: '100%',
            height: 400,
            type: 'pie',
        },
        labels: @json($chartLabels),
        colors: @json(array_slice($chartColors, 0, count($chartData))),
        legend: {
            position: 'right'
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: '{{ config("currency_model") }}',
                        minimumFractionDigits: 2
                    }).format(val);
                }
            }
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    width: '100%'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Initialize chart after DOM and scripts are loaded
    // راه‌اندازی نمودار پس از بارگذاری DOM و اسکریپت‌ها
    function initRevenueChart() {
        const chartElement = document.querySelector("#revenue-breakdown-chart");
        if (!chartElement) {
            console.error('Chart container not found');
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
            if (window.revenueBreakdownChartInstance) {
                window.revenueBreakdownChartInstance.destroy();
            }
            
            window.revenueBreakdownChartInstance = new ApexCharts(chartElement, revenueBreakdownOptions);
            window.revenueBreakdownChartInstance.render();
        } catch (error) {
            console.error('Error rendering chart:', error);
            chartElement.innerHTML = '<div class="text-center p-4"><p class="text-danger">Error rendering chart: ' + error.message + '</p></div>';
        }
    }
    
    // Wait for DOM and ApexCharts to be ready
    // منتظر ماندن برای آماده بودن DOM و ApexCharts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for ApexCharts script to load
            // کمی صبر برای بارگذاری اسکریپت ApexCharts
            setTimeout(initRevenueChart, 100);
        });
    } else {
        // DOM already loaded
        setTimeout(initRevenueChart, 100);
    }
    @else
    // No data to display
    // هیچ داده‌ای برای نمایش وجود ندارد
    document.addEventListener('DOMContentLoaded', function() {
        const chartContainer = document.querySelector("#revenue-breakdown-chart");
        if (chartContainer) {
            chartContainer.innerHTML = '<div class="text-center p-4"><p class="text-muted">{{ translate("No revenue data available for the selected period") }}</p></div>';
        }
    });
    @endif
</script>
@endpush

