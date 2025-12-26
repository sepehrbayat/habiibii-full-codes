@extends('layouts.app')

@section('title', translate('Search Salons'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.APP_URL = '{{ url('/') }}';
        window.translate = function(key) {
            // Translation helper - can be enhanced with actual translation system
            // کمک‌کننده ترجمه - می‌تواند با سیستم ترجمه واقعی بهبود یابد
            const translations = {
                'Find Your Perfect Salon': '{{ translate('Find Your Perfect Salon') }}',
                'Search and book appointments at top-rated salons': '{{ translate('Search and book appointments at top-rated salons') }}',
                'Search': '{{ translate('Search') }}',
                'Salon name...': '{{ translate('Salon name...') }}',
                'Category': '{{ translate('Category') }}',
                'All Categories': '{{ translate('All Categories') }}',
                'Type': '{{ translate('Type') }}',
                'All': '{{ translate('All') }}',
                'Salon': '{{ translate('Salon') }}',
                'Clinic': '{{ translate('Clinic') }}',
                'Sort By': '{{ translate('Sort By') }}',
                'Highest Rated': '{{ translate('Highest Rated') }}',
                'Nearest': '{{ translate('Nearest') }}',
                'Most Popular': '{{ translate('Most Popular') }}',
                'View Details': '{{ translate('View Details') }}',
                'reviews': '{{ translate('reviews') }}',
                'bookings': '{{ translate('bookings') }}',
                'No salons found. Try adjusting your search criteria.': '{{ translate('No salons found. Try adjusting your search criteria.') }}',
            };
            return translations[key] || key;
        };
    </script>
    <link rel="stylesheet" href="{{ mix('css/beauty-booking.css', 'public') }}">
@endpush

@section('content')
    <!-- React App Container -->
    <!-- Container اپلیکیشن React -->
    <div id="beauty-salon-search-root"></div>

    <!-- Fallback for non-JS users -->
    <!-- Fallback برای کاربران بدون JavaScript -->
    <noscript>
        <div class="container py-4">
            <div class="alert alert-warning">
                {{ translate('JavaScript is required to use this page. Please enable JavaScript in your browser.') }}
            </div>
        </div>
    </noscript>
@endsection

@push('script')
    <script src="{{ mix('js/beauty-booking.js', 'public') }}" defer></script>
@endpush

