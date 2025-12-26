@extends('layouts.admin.app')

@section('title', translate('messages.how_to_manage_subscriptions'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/beauty/subscription.png') }}" class="w--22" alt="">
                    </span>
                    <span>{{translate('messages.how_to_manage_subscriptions')}}
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
                    <h3>{{ translate('messages.subscription_management_overview') }}</h3>
                    <p>{{ translate('messages.subscription_management_description') }}</p>

                    <h4 class="mt-4">{{ translate('messages.subscription_types') }}</h4>
                    <ol>
                        <li><strong>{{ translate('messages.featured_listing') }}:</strong> {{ translate('messages.featured_listing_description') }}</li>
                        <li><strong>{{ translate('messages.boost_ads') }}:</strong> {{ translate('messages.boost_ads_description') }}</li>
                        <li><strong>{{ translate('messages.banner_ads') }}:</strong> {{ translate('messages.banner_ads_description') }}</li>
                        <li><strong>{{ translate('messages.advanced_dashboard') }}:</strong> {{ translate('messages.advanced_dashboard_description') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.viewing_subscriptions') }}</h4>
                    <ol>
                        <li>{{ translate('messages.navigate_to_subscription_management') }}</li>
                        <li>{{ translate('messages.view_all_active_subscriptions') }}</li>
                        <li>{{ translate('messages.filter_by_type_or_status') }}</li>
                        <li>{{ translate('messages.view_subscription_details') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.subscription_duration') }}</h4>
                    <ul>
                        <li>{{ translate('messages.featured_listing_7_or_30_days') }}</li>
                        <li>{{ translate('messages.boost_ads_7_or_30_days') }}</li>
                        <li>{{ translate('messages.banner_ads_monthly') }}</li>
                        <li>{{ translate('messages.advanced_dashboard_monthly_or_yearly') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.subscription_statuses') }}</h4>
                    <ul>
                        <li><strong>{{ translate('messages.active') }}:</strong> {{ translate('messages.active_subscription_description') }}</li>
                        <li><strong>{{ translate('messages.pending') }}:</strong> {{ translate('messages.pending_subscription_description') }}</li>
                        <li><strong>{{ translate('messages.expired') }}:</strong> {{ translate('messages.expired_subscription_description') }}</li>
                        <li><strong>{{ translate('messages.cancelled') }}:</strong> {{ translate('messages.cancelled_subscription_description') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.important_notes') }}</h4>
                    <ul>
                        <li>{{ translate('messages.subscriptions_auto_expire_on_end_date') }}</li>
                        <li>{{ translate('messages.badges_removed_on_expiry') }}</li>
                        <li>{{ translate('messages.vendors_receive_expiry_notifications') }}</li>
                        <li>{{ translate('messages.subscription_prices_configurable') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

