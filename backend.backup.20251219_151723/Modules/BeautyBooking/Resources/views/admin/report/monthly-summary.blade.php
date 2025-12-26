@extends('layouts.admin.app')

@section('title', translate('Monthly Summary'))

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
                        <span>{{ translate('messages.Monthly_Performance_Summary') }}
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
                            <label class="form-label">{{ translate('Select Year') }}</label>
                            <select name="year" class="form-control">
                                @for($y = now()->year; $y >= now()->year - 4; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
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
                        <h3>{{ number_format($totals['bookings']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Total Bookings') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($totals['revenue'], 2) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Total Revenue') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($totals['service_fee'], 2) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Service Fees') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($totals['avg_cancellation_rate'], 1) }}%</h3>
                        <p class="text-muted mb-0">{{ translate('Avg. Cancellation Rate') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Bookings & Revenue Trend') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthly-performance-chart" style="height: 360px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Cancellation Rate vs Service/Commission') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthly-secondary-chart" style="height: 360px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Table -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ translate('Monthly Breakdown for') }} {{ $year }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Month') }}</th>
                                <th>{{ translate('Bookings') }}</th>
                                <th>{{ translate('Revenue') }}</th>
                                <th>{{ translate('Service Fee') }}</th>
                                <th>{{ translate('Commission') }}</th>
                                <th>{{ translate('Cancellations') }}</th>
                                <th>{{ translate('Cancellation Rate') }}</th>
                                <th>{{ translate('New Customers') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ number_format($row['bookings']) }}</td>
                                    <td>{{ number_format($row['revenue'], 2) }}</td>
                                    <td>{{ number_format($row['service_fee'], 2) }}</td>
                                    <td>{{ number_format($row['commission'], 2) }}</td>
                                    <td>{{ number_format($row['cancellations']) }}</td>
                                    <td>{{ number_format($row['cancellation_rate'], 1) }}%</td>
                                    <td>{{ number_format($row['new_customers']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";

    const monthlyPerformanceOptions = {
        chart: {
            height: 330,
            type: 'line',
            toolbar: { show: false }
        },
        stroke: {
            curve: 'smooth',
            width: [3, 3]
        },
        series: [
            {
                name: '{{ translate('Bookings') }}',
                type: 'column',
                data: @json($chartData['bookings'])
            },
            {
                name: '{{ translate('Revenue') }}',
                type: 'line',
                data: @json($chartData['revenue'])
            }
        ],
        labels: @json($chartData['labels']),
        yaxis: [
            {
                title: { text: '{{ translate('Bookings') }}' }
            },
            {
                opposite: true,
                title: { text: '{{ translate('Revenue') }}' }
            }
        ],
        colors: ['#00aa96', '#005555'],
        dataLabels: { enabled: false }
    };

    const monthlySecondaryOptions = {
        chart: {
            height: 330,
            type: 'line',
            toolbar: { show: false }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        series: [
            {
                name: '{{ translate('Service Fee') }}',
                data: @json($chartData['service_fee'])
            },
            {
                name: '{{ translate('Commission') }}',
                data: @json($chartData['commission'])
            },
            {
                name: '{{ translate('Cancellation Rate') }} (%)',
                data: @json($chartData['cancellation_rate'])
            }
        ],
        labels: @json($chartData['labels']),
        colors: ['#2ecc71', '#e67e22', '#e74c3c'],
        dataLabels: { enabled: false }
    };

    new ApexCharts(document.querySelector("#monthly-performance-chart"), monthlyPerformanceOptions).render();
    new ApexCharts(document.querySelector("#monthly-secondary-chart"), monthlySecondaryOptions).render();
</script>
@endpush
