@extends('layouts.admin.app')

@section('title', translate('messages.beauty_dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('/public/assets/admin/img/grocery.svg')}}" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">{{translate('messages.beauty_booking_overview')}}</h1>
                            <p class="page-header-text m-0">{{translate('messages.beauty_booking_overview_subtitle')}}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-auto">
                    <span class="badge badge-soft-primary">{{ translate('messages.module') }}: Beauty Booking</span>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- KPI Cards -->
        <div class="row g-2 mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.total_bookings') }}</h6>
                    <h3 class="count">{{ number_format($totalBookings) }}</h3>
                    <div class="subtxt">{{ translate('messages.all_time') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.active_salons') }}</h6>
                    <h3 class="count">{{ number_format($activeSalons) }}</h3>
                    <div class="subtxt">{{ number_format($totalSalons) }} {{ translate('messages.total') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.total_revenue') }}</h6>
                    <h3 class="count">{{ \App\CentralLogics\Helpers::currency_symbol() }}{{ number_format($totalRevenue, 2) }}</h3>
                    <div class="subtxt">{{ translate('messages.this_month') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/customers.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.pending_reviews') }}</h6>
                    <h3 class="count">{{ number_format($pendingReviews) }}</h3>
                    <div class="subtxt">{{ translate('messages.awaiting_moderation') }}</div>
                </div>
            </div>
        </div>

        <!-- Additional KPI Cards -->
        <div class="row g-2 mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.cancellation_rate') }}</h6>
                    <h3 class="count">{{ number_format($cancellationRate, 2) }}%</h3>
                    <div class="subtxt">{{ translate('messages.last_30_days') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.pending_verifications') }}</h6>
                    <h3 class="count">{{ number_format($pendingVerifications) }}</h3>
                    <div class="subtxt">{{ translate('messages.salons_awaiting_approval') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.loyalty_points_issued') }}</h6>
                    <h3 class="count">{{ number_format($loyaltyPointsIssued) }}</h3>
                    <div class="subtxt">{{ translate('messages.this_month') }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="__dashboard-card-2">
                    <img src="{{asset('/public/assets/admin/img/dashboard/food/customers.svg')}}" alt="dashboard">
                    <h6 class="name">{{ translate('messages.quick_actions') }}</h6>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.beautybooking.review.list') }}" class="btn btn-sm btn-primary">
                            {{ translate('messages.moderate_reviews') }}
                        </a>
                        <a href="{{ route('admin.beautybooking.salon.list', ['verification_status' => 0]) }}" class="btn btn-sm btn-soft-primary">
                            {{ translate('messages.view_pending') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-2 mb-3">
            <!-- Revenue Breakdown Pie Chart -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.revenue_by_model') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="revenue-breakdown-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Bookings Over Time Line Chart -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.bookings_over_time') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="bookings-over-time-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Salons Bar Chart -->
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.top_performing_salons') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="top-salons-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row g-2">
            <!-- Recent Bookings -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.recent_bookings') }}</h5>
                        <a href="{{ route('admin.beautybooking.booking.list') }}" class="btn btn-sm btn-soft-primary">
                            {{ translate('messages.view_all') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ translate('messages.booking_reference') }}</th>
                                        <th>{{ translate('messages.customer') }}</th>
                                        <th>{{ translate('messages.salon') }}</th>
                                        <th>{{ translate('messages.status') }}</th>
                                        <th>{{ translate('messages.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBookings as $booking)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.beautybooking.booking.view', $booking->id) }}">
                                                    #{{ $booking->booking_reference }}
                                                </a>
                                            </td>
                                            <td>{{ $booking->user->f_name ?? 'N/A' }}</td>
                                            <td>{{ $booking->salon->store->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-soft-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $booking->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">{{ translate('messages.no_recent_bookings') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Verifications -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.pending_salon_verifications') }}</h5>
                        <a href="{{ route('admin.beautybooking.salon.list', ['verification_status' => 0]) }}" class="btn btn-sm btn-soft-primary">
                            {{ translate('messages.view_all') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ translate('messages.salon_name') }}</th>
                                        <th>{{ translate('messages.business_type') }}</th>
                                        <th>{{ translate('messages.submitted') }}</th>
                                        <th>{{ translate('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSalonVerifications as $salon)
                                        <tr>
                                            <td>{{ $salon->store->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-soft-info">
                                                    {{ ucfirst($salon->business_type) }}
                                                </span>
                                            </td>
                                            <td>{{ $salon->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.beautybooking.salon.view', $salon->id) }}" class="btn btn-sm btn-primary">
                                                    {{ translate('messages.review') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ translate('messages.no_pending_verifications') }}</td>
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
@endpush

@push('script_2')
    <script>
        "use strict";

        // Revenue Breakdown Pie Chart
        // نمودار دایره‌ای تفکیک درآمد
        const revenueData = @json($revenueByModel);
        const revenueLabels = Object.keys(revenueData);
        const revenueValues = Object.values(revenueData);

        const revenueOptions = {
            series: revenueValues,
            chart: {
                type: 'pie',
                height: 350
            },
            labels: revenueLabels,
            colors: ['#005555', '#76ffcd', '#ff6d6d', '#f0ad4e', '#5bc0de', '#5cb85c', '#d9534f', '#337ab7', '#8e44ad', '#e74c3c'],
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "{{ \App\CentralLogics\Helpers::currency_symbol() }}" + val.toFixed(2);
                    }
                }
            }
        };

        const revenueChart = new ApexCharts(document.querySelector("#revenue-breakdown-chart"), revenueOptions);
        revenueChart.render();

        // Bookings Over Time Line Chart
        // نمودار خطی رزروها در طول زمان
        const bookingsData = @json($bookingsOverTime);
        const bookingLabels = @json($bookingLabels);

        const bookingsOptions = {
            series: [{
                name: '{{ translate("messages.bookings") }}',
                data: bookingsData
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#005555'],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: bookingLabels
            },
            yaxis: {
                title: {
                    text: '{{ translate("messages.number_of_bookings") }}'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' {{ translate("messages.bookings") }}';
                    }
                }
            }
        };

        const bookingsChart = new ApexCharts(document.querySelector("#bookings-over-time-chart"), bookingsOptions);
        bookingsChart.render();

        // Top Salons Bar Chart
        // نمودار میله‌ای سالن‌های برتر
        const topSalonNames = @json($topSalonNames);
        const topSalonBookings = @json($topSalonBookings);

        const topSalonsOptions = {
            series: [{
                name: '{{ translate("messages.bookings") }}',
                data: topSalonBookings
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#76ffcd'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: topSalonNames
            },
            yaxis: {
                title: {
                    text: '{{ translate("messages.number_of_bookings") }}'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' {{ translate("messages.bookings") }}';
                    }
                }
            }
        };

        const topSalonsChart = new ApexCharts(document.querySelector("#top-salons-chart"), topSalonsOptions);
        topSalonsChart.render();
    </script>
@endpush
