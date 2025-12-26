@extends('layouts.admin.app')

@section('title', translate('messages.how_to_generate_reports'))

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
                    <span>{{translate('messages.how_to_generate_reports')}}
                    </span>
                </h1>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ translate('messages.report_generation_overview') }}</h3>
                    <p>{{ translate('messages.report_generation_description') }}</p>

                    <h4 class="mt-4">{{ translate('messages.available_reports') }}</h4>
                    <ol>
                        <li><strong>{{ translate('messages.financial_report') }}:</strong> {{ translate('messages.financial_report_description') }}</li>
                        <li><strong>{{ translate('messages.revenue_breakdown') }}:</strong> {{ translate('messages.revenue_breakdown_description') }}</li>
                        <li><strong>{{ translate('messages.monthly_summary') }}:</strong> {{ translate('messages.monthly_summary_description') }}</li>
                        <li><strong>{{ translate('messages.package_usage') }}:</strong> {{ translate('messages.package_usage_description') }}</li>
                        <li><strong>{{ translate('messages.loyalty_stats') }}:</strong> {{ translate('messages.loyalty_stats_description') }}</li>
                        <li><strong>{{ translate('messages.top_rated_monthly') }}:</strong> {{ translate('messages.top_rated_monthly_description') }}</li>
                        <li><strong>{{ translate('messages.trending_clinics') }}:</strong> {{ translate('messages.trending_clinics_description') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.generating_reports') }}</h4>
                    <ol>
                        <li>{{ translate('messages.navigate_to_reports_section') }}</li>
                        <li>{{ translate('messages.select_report_type') }}</li>
                        <li>{{ translate('messages.select_date_range') }}</li>
                        <li>{{ translate('messages.apply_filters_if_needed') }}</li>
                        <li>{{ translate('messages.click_generate_report') }}</li>
                        <li>{{ translate('messages.view_or_export_report') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.report_filters') }}</h4>
                    <ul>
                        <li>{{ translate('messages.filter_by_date_range') }}</li>
                        <li>{{ translate('messages.filter_by_salon') }}</li>
                        <li>{{ translate('messages.filter_by_status') }}</li>
                        <li>{{ translate('messages.filter_by_revenue_model') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.exporting_reports') }}</h4>
                    <ul>
                        <li>{{ translate('messages.reports_can_be_exported_to_excel') }}</li>
                        <li>{{ translate('messages.reports_can_be_exported_to_pdf') }}</li>
                        <li>{{ translate('messages.export_includes_all_filtered_data') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.important_notes') }}</h4>
                    <ul>
                        <li>{{ translate('messages.reports_include_all_revenue_models') }}</li>
                        <li>{{ translate('messages.commission_calculations_included') }}</li>
                        <li>{{ translate('messages.reports_updated_in_real_time') }}</li>
                        <li>{{ translate('messages.large_date_ranges_may_take_time') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

