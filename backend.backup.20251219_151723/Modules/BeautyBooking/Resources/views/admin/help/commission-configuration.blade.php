@extends('layouts.admin.app')

@section('title', translate('messages.how_to_configure_commissions'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/category.png') }}" class="w--22" alt="">
                    </span>
                    <span>{{ translate('messages.how_to_configure_commissions') }}
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
                    <h3>{{ translate('messages.commission_configuration_overview') }}</h3>
                    <p>{{ translate('messages.commission_configuration_description') }}</p>

                    <h4 class="mt-4">{{ translate('messages.revenue_models') }}</h4>
                    <p>{{ translate('messages.beauty_booking_has_10_revenue_models') }}</p>
                    <ol>
                        <li><strong>{{ translate('messages.variable_commission') }}:</strong> {{ translate('messages.variable_commission_description') }}</li>
                        <li><strong>{{ translate('messages.subscription') }}:</strong> {{ translate('messages.subscription_description') }}</li>
                        <li><strong>{{ translate('messages.advertising') }}:</strong> {{ translate('messages.advertising_description') }}</li>
                        <li><strong>{{ translate('messages.service_fee') }}:</strong> {{ translate('messages.service_fee_description') }}</li>
                        <li><strong>{{ translate('messages.packages') }}:</strong> {{ translate('messages.packages_description') }}</li>
                        <li><strong>{{ translate('messages.cancellation_fee') }}:</strong> {{ translate('messages.cancellation_fee_description') }}</li>
                        <li><strong>{{ translate('messages.consultation') }}:</strong> {{ translate('messages.consultation_description') }}</li>
                        <li><strong>{{ translate('messages.cross_selling') }}:</strong> {{ translate('messages.cross_selling_description') }}</li>
                        <li><strong>{{ translate('messages.retail') }}:</strong> {{ translate('messages.retail_description') }}</li>
                        <li><strong>{{ translate('messages.gift_cards') }}:</strong> {{ translate('messages.gift_cards_description') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.configuring_commissions') }}</h4>
                    <ol>
                        <li>{{ translate('messages.navigate_to_commission_settings') }}</li>
                        <li>{{ translate('messages.select_service_category') }}</li>
                        <li>{{ translate('messages.set_commission_percentage') }}</li>
                        <li>{{ translate('messages.set_salon_level_commissions') }}</li>
                        <li>{{ translate('messages.save_changes') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.commission_by_category') }}</h4>
                    <p>{{ translate('messages.different_categories_can_have_different_commissions') }}</p>
                    <ul>
                        <li>{{ translate('messages.salon_services_typically_10_to_20_percent') }}</li>
                        <li>{{ translate('messages.clinic_services_typically_5_to_10_percent') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.commission_by_salon_level') }}</h4>
                    <p>{{ translate('messages.top_rated_salons_may_receive_discount') }}</p>
                    <ul>
                        <li>{{ translate('messages.top_rated_discount_applied') }}</li>
                        <li>{{ translate('messages.discount_configurable_in_settings') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.important_notes') }}</h4>
                    <ul>
                        <li>{{ translate('messages.commission_changes_affect_future_bookings') }}</li>
                        <li>{{ translate('messages.existing_bookings_not_affected') }}</li>
                        <li>{{ translate('messages.min_max_limits_enforced') }}</li>
                        <li>{{ translate('messages.commission_calculated_at_booking_creation') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

