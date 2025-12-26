@extends('layouts.admin.app')

@section('title', translate('messages.beauty_booking_help'))

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
                    <span>{{ translate('messages.beauty_booking_help') }}</span>
                </h1>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row g-2">
        <!-- Salon Approval Help -->
        <div class="col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-book"></i>
                        {{ translate('messages.salon_approval') }}
                    </h5>
                    <p class="card-text">
                        {{ translate('messages.salon_approval_help_description') }}
                    </p>
                    <a href="{{ route('admin.beautybooking.help.salon-approval') }}" class="btn btn-primary">
                        {{ translate('messages.view_guide') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Commission Configuration Help -->
        <div class="col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-settings"></i>
                        {{ translate('messages.commission_configuration') }}
                    </h5>
                    <p class="card-text">
                        {{ translate('messages.commission_configuration_help_description') }}
                    </p>
                    <a href="{{ route('admin.beautybooking.help.commission-configuration') }}" class="btn btn-primary">
                        {{ translate('messages.view_guide') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscription Management Help -->
        <div class="col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-subscription"></i>
                        {{ translate('messages.subscription_management') }}
                    </h5>
                    <p class="card-text">
                        {{ translate('messages.subscription_management_help_description') }}
                    </p>
                    <a href="{{ route('admin.beautybooking.help.subscription-management') }}" class="btn btn-primary">
                        {{ translate('messages.view_guide') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Review Moderation Help -->
        <div class="col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-star"></i>
                        {{ translate('messages.review_moderation') }}
                    </h5>
                    <p class="card-text">
                        {{ translate('messages.review_moderation_help_description') }}
                    </p>
                    <a href="{{ route('admin.beautybooking.help.review-moderation') }}" class="btn btn-primary">
                        {{ translate('messages.view_guide') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Generation Help -->
        <div class="col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-chart-bar"></i>
                        {{ translate('messages.report_generation') }}
                    </h5>
                    <p class="card-text">
                        {{ translate('messages.report_generation_help_description') }}
                    </p>
                    <a href="{{ route('admin.beautybooking.help.report-generation') }}" class="btn btn-primary">
                        {{ translate('messages.view_guide') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

