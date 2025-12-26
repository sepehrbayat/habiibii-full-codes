@extends('layouts.admin.app')

@section('title', translate('messages.Beauty_Booking_Module_Dashboard'))

@section('dashboard')
show active
@endsection

@section('content')
@php
    $mod = \App\Models\Module::find(Config::get('module.current_module_id'));
@endphp
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img class="onerror-image" data-onerror-image="{{ asset('/public/assets/admin/img/grocery.svg') }}"
                             src="{{$mod->icon_full_url }}" width="38" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title text-title mb-0">
                                {{translate($mod->module_name)}} {{translate('messages.Dashboard')}}
                            </h1>
                            <p class="page-header-text text-title fs-12 m-0">{{ translate('messages.Monitor_your') }}
                                <strong class="font-bold"> {{translate($mod->module_name)}} {{ translate('messages.business') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-auto min--280">
                    <select data-src-url="{{ route('admin.beautybooking.dashboard') }}" name="zone_id" class="form-control js-select2-custom  fetch_data_zone_wise" >
                        <option value="all">{{ translate('messages.All_Zones') }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get(['name','id']) as $zone)
                            <option
                                value="{{$zone['id']}}" {{request()->zone_id == $zone['id']?'selected':''}}>
                                {{$zone['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between statistics--title-area">
                    <div class="statistics--title pr-sm-3" id="stat_zone">
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="page-header-title text-title fs-18 mb-0">
                                {{ translate('messages.Booking_Statistics') }}</h3>
                            <label class="badge badge-soft-primary m-0">
                                {{ translate('messages.zone') }} : <span id="zoneName">{{ $zoneName ?? translate('All') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="statistics--select">
                        <select class="custom-select border-0 booking_stats_update" name="statistics_type">
                            <option value="all" {{ request()->statistics_type ? '' : 'selected' }}>
                                {{ translate('messages.All_Time') }}
                            </option>
                            <option value="this_year" {{ request()->statistics_type == 'this_year' ? 'selected' : '' }}>{{ translate('messages.this_year') }}</option>
                            <option value="this_month" {{ request()->statistics_type == 'this_month' ? 'selected' : '' }}>{{ translate('messages.this_month') }}</option>
                            <option value="this_week" {{ request()->statistics_type == 'this_week' ? 'selected' : '' }}>{{ translate('messages.this_week') }}</option>
                        </select>
                    </div>
                </div>
                <div id="bookingStatistics" style="position: relative;">
                    <div id="loading" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ translate('Loading...') }}</span>
                        </div>
                    </div>
                    @include('beautybooking::admin.partials.booking-statistics', [
                        'pendingCount' => $pendingCount ?? 0,
                        'confirmedCount' => $confirmedCount ?? 0,
                        'completedCount' => $completedCount ?? 0,
                        'cancelledCount' => $cancelledCount ?? 0,
                        'totalCount' => $totalCount ?? 0
                    ])
                </div>
            </div>
        </div>

        <!-- End Stats -->
        <div class="row g-2">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center __gap-12px">
                            <div class="__gross-amount" id="gross_earning">
                                <h6 class="gross-earning">{{ \App\CentralLogics\Helpers::format_currency($grossEarning ?? 0) }}</h6>
                                <span>{{ translate('messages.Gross_Earnings') }}</span>
                            </div>
                            <div class="chart--label __chart-label p-0 move-left-100 ml-auto">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{ translate('Earnings') }} ({{ date('Y') }})
                                </span>
                            </div>
                            <select data-src-url="{{ route('admin.beautybooking.dashboard-stats.commission_overview') }}" id="commission_overview_stats_update"
                                class="custom-select border-0 text-center w-auto ml-auto commission_overview_stats_update"
                                name="commission_overview">
                                <option value="all">
                                    {{ translate('All Time') }}
                                </option>
                                <option value="this_year" {{ request()->commission_overview == 'this_year' ? 'selected' : '' }}>
                                    {{ translate('this_year') }}
                                </option>
                                <option value="this_month" {{ request()->commission_overview == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_month') }}
                                </option>
                                <option value="this_week" {{ request()->commission_overview == 'this_week' ? 'selected' : '' }}>
                                    {{ translate('this_week') }}
                                </option>
                            </select>
                        </div>
                        <div id="commission-overview-board" style="position: relative;">
                            <div id="loading-commission" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">{{ translate('Loading...') }}</span>
                                </div>
                            </div>
                            @include('beautybooking::admin.partials.sale-chart')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Card -->
                <div class="card h-100">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <h5 class="card-header-title">
                            {{ translate('Bookings by Status') }}
                        </h5>
                        <select data-src-url="{{ route('admin.beautybooking.dashboard-stats.booking_by_status') }}" id="booking_by_status_stats_update" class="custom-select border-0 text-center w-auto booking_overview_stats_update"
                                name="booking_overview">
                            <option value="all">
                                {{ translate('All Time') }}
                            </option>
                            <option value="this_year" {{ request()->booking_overview == 'this_year' ? 'selected' : '' }}>
                                {{ translate('This year') }}
                            </option>
                            <option value="this_month" {{ request()->booking_overview == 'this_month' ? 'selected' : '' }}>
                                {{ translate('This month') }}
                            </option>
                            <option value="this_week" {{ request()->booking_overview == 'this_week' ? 'selected' : '' }}>
                                {{ translate('This week') }}
                            </option>
                        </select>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body" id="booking-overview-board" style="position: relative;">
                        <div id="loading-booking-overview" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">{{ translate('Loading...') }}</span>
                            </div>
                        </div>
                        @include('beautybooking::admin.partials.by-booking-status', [
                            'pendingCount' => $pendingCount ?? 0,
                            'confirmedCount' => $confirmedCount ?? 0,
                            'completedCount' => $completedCount ?? 0,
                            'cancelledCount' => $cancelledCount ?? 0,
                            'totalCount' => $totalCount ?? 0
                        ])
                    </div>
                    <!-- End Body -->
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-customer-view">
                    <div class="card-header border-0 order-header-shadow">
                        <h5 class="card-header-title font-bold d-flex justify-content-between">
                            <span>{{ translate('messages.top_customers') }}</span>
                        </h5>
                        <a href="{{ route('admin.users.customer.list') }}" class="fz-12px font-semibold text-006AE5">{{ translate('view_all') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="top--selling" id="topCustomers">
                            @include('beautybooking::admin.partials.top-customers', ['topCustomers' => $topCustomers ?? collect()])
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-salon-view">
                    <div class="card-header border-0 order-header-shadow">
                        <h5 class="card-header-title font-bold d-flex justify-content-between">
                            <span>{{ translate('messages.top_salons') }}</span>
                        </h5>
                        <a href="{{ route('admin.beautybooking.salon.list')}}" class="fz-12px font-semibold text-006AE5">{{ translate('view_all') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="top--selling" id="topSalons">
                            @include('beautybooking::admin.partials.top-salons', ['topSalons' => $topSalons ?? collect()])
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

    <div class="d-none" id="current_url" data-src-url="{{ url()->current() }}"> </div>
    <div class="d-none" id="current_currency" data-currency="{{ \App\CentralLogics\Helpers::currency_symbol() }}"></div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{ asset('public/assets/admin') }}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js">
    </script>
    <!-- Apex Charts -->
    <script src="{{ asset('/public/assets/admin/js/apex-charts/apexcharts.js') }}"></script>
    <!-- Apex Charts -->
@endpush

@php
    $pendingCount = $pendingCount ?? 0;
    $confirmedCount = $confirmedCount ?? 0;
    $completedCount = $completedCount ?? 0;
    $cancelledCount = $cancelledCount ?? 0;
    $totalCount = $totalCount ?? 0;
@endphp

@push('script_2')
    <script>
        "use strict";

        const pendingCount = {{ $pendingCount }};
        const confirmedCount = {{ $confirmedCount }};
        const completedCount = {{ $completedCount }};
        const cancelledCount = {{ $cancelledCount }};
        const totalCount = {{ $totalCount }};

        document.addEventListener('DOMContentLoaded', function() {
            initializeDonutChart(pendingCount, confirmedCount, completedCount, cancelledCount, totalCount);
            @if(isset($total_sell) && isset($commission) && isset($total_subs) && isset($label))
            const initialTotalSell = [{{ implode(",", array_map(fn($val) => number_format($val, 2, '.', ''), array_values($total_sell))) }}];
            const initialCommission = [{{ implode(",", array_map(fn($val) => number_format($val, 2, '.', ''), array_values($commission))) }}];
            const initialTotalSubs = [{{ implode(",", array_map(fn($val) => number_format($val, 2, '.', ''), array_values($total_subs))) }}];
            const initialLabels = [{!! implode(",", $label) !!}];
            initializeAreaChart(initialTotalSell, initialCommission, initialTotalSubs, initialLabels);
            @endif
        });

        function initializeDonutChart(pendingCount, confirmedCount, completedCount, cancelledCount, totalCount) {
            let options;
            let chart;

            options = {
                series: [pendingCount, confirmedCount, completedCount, cancelledCount],
                chart: {
                    width: 320,
                    type: 'donut',
                },
                labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                dataLabels: {
                    enabled: false,
                },
                responsive: [{
                    breakpoint: 1650,
                    options: {
                        chart: {
                            width: 250
                        },
                    }
                }],
                colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545'],
                fill: {
                    colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                },
                legend: {
                    show: false
                },
            };

            if (chart) {
                chart.destroy();
            }

            chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
            chart.render();
        }

        function initializeAreaChart(totalSell, commission, totalSubs, labels) {
            let ApexChart;
            const options = {
                series: [{
                    name: 'Gross Earning',
                    data: totalSell
                }, {
                    name: 'Commission Earning',
                    data: commission
                }, {
                    name: 'Subscription Earning',
                    data: totalSubs
                }],
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    colors: ['#76ffcd','#ff6d6d', '#005555'],
                },
                dataLabels: {
                    enabled: false,
                    colors: ['#76ffcd','#ff6d6d', '#005555'],
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                    colors: ['#76ffcd','#ff6d6d', '#005555'],
                },
                fill: {
                    type: 'gradient',
                    colors: ['#76ffcd','#ff6d6d', '#005555'],
                },
                xaxis: {
                    categories: labels
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy HH:mm'
                    },
                },
            };

            if (ApexChart) {
                ApexChart.destroy();
            }

            ApexChart = new ApexCharts(document.querySelector("#grow-sale-chart"), options);
            ApexChart.render();
        }
    </script>
    <!-- Beauty Error Handler -->
    <script src="{{ asset('Modules/BeautyBooking/public/assets/js/beauty-error-handler.js') }}"></script>
    @if(file_exists(module_path('BeautyBooking') . '/public/assets/js/admin/view-pages/dashboard.js'))
        <script src="{{asset('Modules/BeautyBooking/public/assets/js/admin/view-pages/dashboard.js')}}"></script>
    @endif
@endpush
