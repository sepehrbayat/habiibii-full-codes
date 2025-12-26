@extends('layouts.admin.app')

@section('title', translate('Package Usage Report'))

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
                        <span>{{ translate('messages.Package_Usage_Sales') }}
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

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['total_packages']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Total Packages') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['active_packages']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Active Packages') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['packages_sold']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Packages Sold (Period)') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['package_revenue'], 2) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Revenue from Packages') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['sessions_redeemed']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Sessions Redeemed') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>{{ number_format($stats['pending_sessions']) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Pending Sessions') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        @php
                            $avgSessionsPerPackage = $stats['packages_sold'] > 0 
                                ? ($stats['sessions_redeemed'] / max($stats['packages_sold'], 1)) 
                                : 0;
                        @endphp
                        <h3>{{ number_format($avgSessionsPerPackage, 1) }}</h3>
                        <p class="text-muted mb-0">{{ translate('Avg. Sessions Used per Package') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Package Usage Trend') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="package-usage-trend" style="height: 360px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ translate('Top Selling Packages') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('Package') }}</th>
                                        <th class="border-0">{{ translate('Salon') }}</th>
                                        <th class="border-0 text-right">{{ translate('Sold') }}</th>
                                        <th class="border-0 text-right">{{ translate('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topPackages as $row)
                                        <tr>
                                            <td><span class="text--title">{{ $row->package->name ?? translate('N/A') }}</span></td>
                                            <td><span class="text--title">{{ $row->package->salon->store->name ?? '-' }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ number_format($row->total_sales) }}</span></td>
                                            <td class="text-right"><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($row->total_revenue) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <div class="empty--data">
                                                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                                                    <h5>{{ translate('No package data found for the selected period') }}</h5>
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

@push('script_2')
<script>
    "use strict";

    const packageUsageTrend = {
        chart: {
            height: 330,
            type: 'area',
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        series: [
            {
                name: '{{ translate('Sessions Used') }}',
                data: @json($usageTrend['data'])
            }
        ],
        labels: @json($usageTrend['labels']),
        colors: ['#005555'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        }
    };

    new ApexCharts(document.querySelector('#package-usage-trend'), packageUsageTrend).render();
</script>
@endpush
