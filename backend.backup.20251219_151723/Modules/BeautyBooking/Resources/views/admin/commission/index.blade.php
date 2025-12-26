@extends('layouts.admin.app')

@section('title', translate('Commission Settings'))

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
                            <img src="{{ asset('public/assets/admin/img/category.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Commission_Settings') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Revenue Models Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs nav-tabs-custom" id="revenueModelsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="variable-commission-tab" data-toggle="tab" href="#variable-commission" role="tab">
                            {{ translate('1. Variable Commission') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="service-fee-tab" data-toggle="tab" href="#service-fee" role="tab">
                            {{ translate('2. Service Fee') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="subscription-tab" data-toggle="tab" href="#subscription" role="tab">
                            {{ translate('3. Subscription/Ads') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="package-tab" data-toggle="tab" href="#package" role="tab">
                            {{ translate('4. Package Sales') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cancellation-tab" data-toggle="tab" href="#cancellation" role="tab">
                            {{ translate('5. Cancellation Fee') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="consultation-tab" data-toggle="tab" href="#consultation" role="tab">
                            {{ translate('6. Consultation') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cross-selling-tab" data-toggle="tab" href="#cross-selling" role="tab">
                            {{ translate('7. Cross-selling') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="retail-tab" data-toggle="tab" href="#retail" role="tab">
                            {{ translate('8. Retail Sales') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="gift-card-tab" data-toggle="tab" href="#gift-card" role="tab">
                            {{ translate('9. Gift Cards') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="loyalty-tab" data-toggle="tab" href="#loyalty" role="tab">
                            {{ translate('10. Loyalty') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="revenueModelsTabsContent">
                    <!-- Tab 1: Variable Commission -->
                    <div class="tab-pane fade show active" id="variable-commission" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Variable Commission by Category/Salon Level') }}</h5>
                        <button type="button" class="btn btn--primary mb-3" data-toggle="modal" data-target="#addCommissionModal">
                            <i class="tio-add"></i> {{ translate('Add Commission Rule') }}
                        </button>
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                    data-hs-datatables-options='{
                                        "order": [],
                                        "orderCellsTop": true,
                                        "paging":false
                                    }'>
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{translate('sl')}}</th>
                                        <th class="border-0">{{ translate('messages.Category') }}</th>
                                        <th class="border-0">{{ translate('messages.Salon_Level') }}</th>
                                        <th class="border-0">{{ translate('messages.Commission') }} (%)</th>
                                        <th class="border-0">{{ translate('messages.Min_Max') }}</th>
                                        <th class="border-0">{{ translate('messages.status') }}</th>
                                        <th class="text-center border-0">{{ translate('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="set-rows">
                                    @foreach($settings as $key=>$setting)
                                        <tr>
                                            <td>{{$key+$settings->firstItem()}}</td>
                                            <td><span class="text--title">{{ $setting->category->name ?? translate('All Categories') }}</span></td>
                                            <td><span class="text--title">{{ ucfirst($setting->salon_level ?? translate('All Levels')) }}</span></td>
                                            <td><strong class="text--title">{{ $setting->commission_percentage }}%</strong></td>
                                            <td>
                                                <span class="text--title">
                                                    {{ \App\CentralLogics\Helpers::format_currency($setting->min_commission) }} - 
                                                    {{ $setting->max_commission ? \App\CentralLogics\Helpers::format_currency($setting->max_commission) : translate('No Limit') }}
                                                </span>
                                            </td>
                                            <td>
                                                <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$setting->id}}">
                                                    <input type="checkbox" data-url="{{route('admin.beautybooking.commission.status',[$setting->id])}}" class="toggle-switch-input status_change_alert" id="statusCheckbox{{$setting->id}}" {{$setting->status?'checked':''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="btn--container justify-content-center">
                                                    <button type="button" class="btn action-btn btn--primary btn-outline-primary" onclick="editCommission({{ $setting->id }})">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                                        data-id="commission-{{$setting->id}}" data-message="{{translate('You want to remove this commission setting')}}">
                                                        <i class="tio-delete-outlined"></i>
                                                    </a>
                                                    <form action="{{ route('admin.beautybooking.commission.delete', $setting->id) }}" method="post" id="commission-{{$setting->id}}">
                                                        @csrf @method('delete')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($settings) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $settings->withQueryString()->links() !!}
                        </div>
                        @if(count($settings) === 0)
                        <div class="empty--data">
                            <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                    </div>

                    <!-- Tab 2: Service Fee -->
                    <div class="tab-pane fade" id="service-fee" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Service Fee (Charged to Customers)') }}</h5>
                        <p class="text-muted mb-3">{{ translate('Service fee is 1-3% of booking amount, charged to customers') }}</p>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Service Fee Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_service_fee_percentage" class="form-control" 
                                           value="{{ config('beautybooking.service_fee.percentage', 2.0) }}" 
                                           step="0.1" min="1" max="3" required>
                                    <small class="text-muted">{{ translate('Range: 1% - 3%') }}</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 3: Subscription/Advertisement -->
                    <div class="tab-pane fade" id="subscription" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Subscription & Advertisement Pricing') }}</h5>
                        <p class="text-muted mb-3">{{ translate('Configure pricing for featured listings, boost ads, banners, and dashboard subscriptions') }}</p>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Featured Listing') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('7 Days Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_featured_7_days" 
                                               class="form-control" value="{{ config('beautybooking.subscription.featured.7_days', 50000) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('30 Days Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_featured_30_days" 
                                               class="form-control" value="{{ config('beautybooking.subscription.featured.30_days', 150000) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Boost Ads') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('7 Days Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_boost_7_days" 
                                               class="form-control" value="{{ config('beautybooking.subscription.boost.7_days', 75000) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('30 Days Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_boost_30_days" 
                                               class="form-control" value="{{ config('beautybooking.subscription.boost.30_days', 200000) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Banner Ads') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('Homepage Banner (Monthly)') }}</label>
                                        <input type="number" name="beauty_booking_subscription_banner_homepage" 
                                               class="form-control" value="{{ config('beautybooking.subscription.banner.homepage', 100000) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Category Banner (Monthly)') }}</label>
                                        <input type="number" name="beauty_booking_subscription_banner_category" 
                                               class="form-control" value="{{ config('beautybooking.subscription.banner.category', 75000) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Search Results Banner (Monthly)') }}</label>
                                        <input type="number" name="beauty_booking_subscription_banner_search_results" 
                                               class="form-control" value="{{ config('beautybooking.subscription.banner.search_results', 60000) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Advanced Dashboard') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('Monthly Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_dashboard_monthly" 
                                               class="form-control" value="{{ config('beautybooking.subscription.dashboard.monthly', 50000) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Yearly Price') }}</label>
                                        <input type="number" name="beauty_booking_subscription_dashboard_yearly" 
                                               class="form-control" value="{{ config('beautybooking.subscription.dashboard.yearly', 500000) }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save All Settings') }}</button>
                        </form>
                    </div>

                    <!-- Tab 4: Package Commission -->
                    <div class="tab-pane fade" id="package" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Package Sales Commission') }}</h5>
                        <p class="text-muted mb-3">{{ translate('Commission is calculated on total package price') }}</p>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission on Total Package Price') }}</label>
                                    <select name="beauty_booking_package_commission_on_total" class="form-control">
                                        <option value="1" {{ config('beautybooking.package.commission_on_total', true) ? 'selected' : '' }}>
                                            {{ translate('Yes') }}
                                        </option>
                                        <option value="0" {{ !config('beautybooking.package.commission_on_total', true) ? 'selected' : '' }}>
                                            {{ translate('No') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 5: Cancellation Fee -->
                    <div class="tab-pane fade" id="cancellation" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Cancellation Fee Thresholds & Percentages') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Time Thresholds (Hours)') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('No Fee If Cancelled Before (Hours)') }}</label>
                                        <input type="number" name="beauty_booking_cancellation_no_fee_hours" 
                                               class="form-control" value="{{ config('beautybooking.cancellation_fee.time_thresholds.no_fee_hours', 24) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Partial Fee Threshold (Hours)') }}</label>
                                        <input type="number" name="beauty_booking_cancellation_partial_fee_hours" 
                                               class="form-control" value="{{ config('beautybooking.cancellation_fee.time_thresholds.partial_fee_hours', 2) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2">{{ translate('Fee Percentages') }}</h6>
                                    <div class="form-group">
                                        <label>{{ translate('No Fee (%)') }}</label>
                                        <input type="number" name="beauty_booking_cancellation_fee_none" 
                                               class="form-control" value="{{ config('beautybooking.cancellation_fee.fee_percentages.no_fee', 0) }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Partial Fee (%)') }}</label>
                                        <input type="number" name="beauty_booking_cancellation_fee_partial" 
                                               class="form-control" value="{{ config('beautybooking.cancellation_fee.fee_percentages.partial', 50) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ translate('Full Fee (%)') }}</label>
                                        <input type="number" name="beauty_booking_cancellation_fee_full" 
                                               class="form-control" value="{{ config('beautybooking.cancellation_fee.fee_percentages.full', 100) }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 6: Consultation -->
                    <div class="tab-pane fade" id="consultation" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Consultation Service Commission') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_consultation_commission" 
                                           class="form-control" value="{{ config('beautybooking.consultation.commission_percentage', 10.0) }}" 
                                           step="0.1" min="0" max="100">
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 7: Cross-selling -->
                    <div class="tab-pane fade" id="cross-selling" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Cross-selling/Upsell Commission') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_cross_selling_commission" 
                                           class="form-control" value="{{ config('beautybooking.cross_selling.commission_percentage', 10.0) }}" 
                                           step="0.1" min="0" max="100">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ translate('Feature Enabled') }}</label>
                                    <select name="beauty_booking_cross_selling_enabled" class="form-control">
                                        <option value="1" {{ config('beautybooking.cross_selling.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Enabled') }}
                                        </option>
                                        <option value="0" {{ !config('beautybooking.cross_selling.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Disabled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 8: Retail -->
                    <div class="tab-pane fade" id="retail" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Retail Sales Commission') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_retail_commission" 
                                           class="form-control" value="{{ config('beautybooking.retail.commission_percentage', 10.0) }}" 
                                           step="0.1" min="0" max="100">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ translate('Feature Enabled') }}</label>
                                    <select name="beauty_booking_retail_enabled" class="form-control">
                                        <option value="1" {{ config('beautybooking.retail.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Enabled') }}
                                        </option>
                                        <option value="0" {{ !config('beautybooking.retail.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Disabled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 9: Gift Card -->
                    <div class="tab-pane fade" id="gift-card" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Gift Card Commission') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_gift_card_commission" 
                                           class="form-control" value="{{ config('beautybooking.gift_card.commission_percentage', 5.0) }}" 
                                           step="0.1" min="0" max="100">
                                    <small class="text-muted">{{ translate('Range: 5% - 10%') }}</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>

                    <!-- Tab 10: Loyalty -->
                    <div class="tab-pane fade" id="loyalty" role="tabpanel">
                        <h5 class="mb-3">{{ translate('Loyalty Campaign Commission') }}</h5>
                        <form action="{{ route('admin.beautybooking.commission.business-settings-update') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>{{ translate('Commission Percentage') }} (%)</label>
                                    <input type="number" name="beauty_booking_loyalty_commission" 
                                           class="form-control" value="{{ config('beautybooking.loyalty.default_commission_percentage', 5.0) }}" 
                                           step="0.1" min="0" max="100">
                                    <small class="text-muted">{{ translate('Range: 5% - 10%') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label>{{ translate('Feature Enabled') }}</label>
                                    <select name="beauty_booking_loyalty_enabled" class="form-control">
                                        <option value="1" {{ config('beautybooking.loyalty.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Enabled') }}
                                        </option>
                                        <option value="0" {{ !config('beautybooking.loyalty.enabled', true) ? 'selected' : '' }}>
                                            {{ translate('Disabled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3">{{ translate('Save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Commission Modal -->
    <div class="modal fade" id="addCommissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add Commission Rule') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.beautybooking.commission.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ translate('Service Category') }} <small class="text-muted">({{ translate('Optional - Leave empty for all categories') }})</small></label>
                            <select name="service_category_id" class="form-control">
                                <option value="">{{ translate('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Salon Level') }} <small class="text-muted">({{ translate('Optional - Leave empty for all levels') }})</small></label>
                            <select name="salon_level" class="form-control">
                                <option value="">{{ translate('All Levels') }}</option>
                                <option value="salon">{{ translate('Salon') }}</option>
                                <option value="clinic">{{ translate('Clinic') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Commission Percentage') }} (%) <span class="text-danger">*</span></label>
                            <input type="number" name="commission_percentage" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Min Commission') }}</label>
                                    <input type="number" name="min_commission" class="form-control" step="0.01" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Max Commission') }} <small class="text-muted">({{ translate('Optional') }})</small></label>
                                    <input type="number" name="max_commission" class="form-control" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Status') }}</label>
                            <select name="status" class="form-control">
                                <option value="1">{{ translate('Active') }}</option>
                                <option value="0">{{ translate('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--reset" data-dismiss="modal">{{ translate('messages.close') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('messages.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    function editCommission(id) {
        // Implement edit functionality
        // This would load the commission setting and populate the edit modal
        console.log('Edit commission:', id);
    }
</script>
@endpush

