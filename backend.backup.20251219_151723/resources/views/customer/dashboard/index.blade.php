@extends('layouts.customer.app')

@section('title', translate('My Dashboard'))

@push('css_or_js')
<link href="{{ asset('public/assets/admin') }}/css/toastr.css" rel="stylesheet">
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .widget-card {
        min-height: 200px;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">{{ translate('My Dashboard') }}</h1>
        @if(addon_published_status('BeautyBooking'))
            <a href="{{ route('beauty-booking.booking.create', 0) }}" class="btn btn-primary">
                <i class="tio-add"></i> {{ translate('Book Appointment') }}
            </a>
        @endif
    </div>
    
    @if(addon_published_status('BeautyBooking'))
        <!-- Beauty Navigation Menu -->
        <!-- منوی ناوبری زیبایی -->
        <div class="card mb-4">
            <div class="card-body">
                <nav class="navbar navbar-expand-lg navbar-light bg-light rounded p-2">
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav mr-auto flex-wrap">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="bookingsDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-calendar-note"></i> {{ translate('Beauty Bookings') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.my-bookings.index') }}">
                                        <i class="tio-book"></i> {{ translate('My Bookings') }}
                                        @if(isset($beautyStats['upcoming_bookings']) && $beautyStats['upcoming_bookings'] > 0)
                                            <span class="badge badge-primary ml-2">{{ $beautyStats['upcoming_bookings'] }}</span>
                                        @endif
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.booking.create', 0) }}">
                                        <i class="tio-add"></i> {{ translate('Create Booking') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.my-bookings.index') }}?status=completed">
                                        <i class="tio-history"></i> {{ translate('Booking History') }}
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-store"></i> {{ translate('Beauty Services') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.search') }}">
                                        <i class="tio-search"></i> {{ translate('Search Salons') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.search') }}?filter=top_rated">
                                        <i class="tio-star"></i> {{ translate('Top Rated Salons') }}
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="packagesDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-package"></i> {{ translate('Packages') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.search') }}">
                                        <i class="tio-package"></i> {{ translate('My Packages') }}
                                        @if(isset($beautyStats['active_packages']) && $beautyStats['active_packages'] > 0)
                                            <span class="badge badge-warning ml-2">{{ $beautyStats['active_packages'] }}</span>
                                        @endif
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.search') }}">
                                        <i class="tio-shopping-cart"></i> {{ translate('Available Packages') }}
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.gift-cards') }}">
                                    <i class="tio-gift"></i> {{ translate('Gift Cards') }}
                                    @if(isset($beautyStats['gift_card_balance']) && $beautyStats['gift_card_balance'] > 0)
                                        <span class="badge badge-danger ml-1">{{ \App\CentralLogics\Helpers::format_currency($beautyStats['gift_card_balance']) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.loyalty') }}">
                                    <i class="tio-star"></i> {{ translate('Loyalty Program') }}
                                    @if(isset($beautyStats['loyalty_points_balance']) && $beautyStats['loyalty_points_balance'] > 0)
                                        <span class="badge badge-warning ml-1">{{ $beautyStats['loyalty_points_balance'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="consultationsDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-chat"></i> {{ translate('Consultations') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.consultations') }}">
                                        <i class="tio-chat"></i> {{ translate('Active Consultations') }}
                                        @if(isset($beautyStats['active_consultations']) && $beautyStats['active_consultations'] > 0)
                                            <span class="badge badge-info ml-2">{{ $beautyStats['active_consultations'] }}</span>
                                        @endif
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.consultations') }}?status=completed">
                                        <i class="tio-history"></i> {{ translate('Consultation History') }}
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="reviewsDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-comment"></i> {{ translate('Reviews') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.reviews') }}">
                                        <i class="tio-comment"></i> {{ translate('My Reviews') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.reviews') }}?status=pending">
                                        <i class="tio-time"></i> {{ translate('Pending Reviews') }}
                                        @if(isset($beautyStats['pending_reviews']) && $beautyStats['pending_reviews'] > 0)
                                            <span class="badge badge-warning ml-2">{{ $beautyStats['pending_reviews'] }}</span>
                                        @endif
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="retailDropdown" role="button" data-toggle="dropdown">
                                    <i class="tio-shopping-cart"></i> {{ translate('Retail Shop') }}
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('beauty-booking.search') }}">
                                        <i class="tio-shopping-cart"></i> {{ translate('Browse Products') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('beauty-booking.retail-orders') }}">
                                        <i class="tio-list"></i> {{ translate('My Orders') }}
                                        @if(isset($beautyStats['retail_orders_count']) && $beautyStats['retail_orders_count'] > 0)
                                            <span class="badge badge-info ml-2">{{ $beautyStats['retail_orders_count'] }}</span>
                                        @endif
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    @endif

    @if(addon_published_status('BeautyBooking'))
        <!-- Beauty Booking Section -->
        <!-- بخش رزرو زیبایی -->
        @include('customer.dashboard.partials.beauty-stats')
        
        <!-- Quick Actions -->
        <!-- اقدامات سریع -->
        @include('customer.dashboard.partials.beauty-quick-actions')
        
        <div class="row g-3 mb-4">
            <!-- Bookings Widget -->
            <!-- ویجت رزروها -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-bookings-widget')
            </div>
            
            <!-- Packages Widget -->
            <!-- ویجت پکیج‌ها -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-packages-widget')
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <!-- Gift Cards Widget -->
            <!-- ویجت کارت‌های هدیه -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-gift-cards-widget')
            </div>
            
            <!-- Loyalty Widget -->
            <!-- ویجت وفاداری -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-loyalty-widget')
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <!-- Charts -->
            <!-- نمودارها -->
            <div class="col-md-12">
                @include('customer.dashboard.partials.beauty-charts')
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <!-- Activity Feed -->
            <!-- فید فعالیت -->
            <div class="col-md-12">
                @include('customer.dashboard.partials.beauty-activity')
            </div>
        </div>
        
        <div class="row g-3">
            <!-- Consultations Widget -->
            <!-- ویجت مشاوره‌ها -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-consultations-widget')
            </div>
            
            <!-- Reviews Widget -->
            <!-- ویجت نظرات -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-reviews-widget')
            </div>
        </div>
        
        <div class="row g-3 mt-3">
            <!-- Retail Orders Widget -->
            <!-- ویجت سفارشات خرده‌فروشی -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-retail-widget')
            </div>
            
            <!-- Wallet Widget -->
            <!-- ویجت کیف پول -->
            <div class="col-md-6">
                @include('customer.dashboard.partials.beauty-wallet-widget')
            </div>
        </div>
    @else
        <!-- No Beauty Module Message -->
        <!-- پیام عدم وجود ماژول زیبایی -->
        <div class="alert alert-info">
            {{ translate('Beauty Booking module is not available') }}
        </div>
    @endif
</div>
@endsection

@push('script')
<script src="{{ asset('public/assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
<script src="{{ asset('/public/assets/admin/js/apex-charts/apexcharts.js') }}"></script>
<script>
    "use strict";
    
    // Initialize charts when beauty module is active
    // مقداردهی اولیه نمودارها زمانی که ماژول زیبایی فعال است
    @if(addon_published_status('BeautyBooking') && isset($beautyCharts) && !empty($beautyCharts))
        document.addEventListener('DOMContentLoaded', function() {
            initializeBeautyCharts();
        });
        
        function initializeBeautyCharts() {
            @if(isset($beautyCharts['booking_trends']))
                initializeBookingTrendsChart();
            @endif
            
            @if(isset($beautyCharts['spending_trends']))
                initializeSpendingTrendsChart();
            @endif
            
            @if(isset($beautyCharts['most_used_services']))
                initializeMostUsedServicesChart();
            @endif
            
            @if(isset($beautyCharts['favorite_salons']))
                initializeFavoriteSalonsChart();
            @endif
            
            @if(isset($beautyCharts['loyalty_trends']))
                initializeLoyaltyTrendsChart();
            @endif
        }
    @endif
</script>
@endpush

