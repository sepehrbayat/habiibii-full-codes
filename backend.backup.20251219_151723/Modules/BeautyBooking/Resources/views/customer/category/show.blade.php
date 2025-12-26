@extends('layouts.app')

@section('title', $category->name ?? translate('Category'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .salon-card {
            transition: transform 0.2s;
        }
        .salon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('beauty-booking.search') }}">{{ translate('Home') }}</a>
            </li>
            @if($category->parent)
                <li class="breadcrumb-item">
                    <a href="{{ route('beauty-booking.category.show', $category->parent->id) }}">
                        {{ $category->parent->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            @if($category->image)
                <img src="{{ asset('storage/app/public/' . $category->image) }}" 
                     alt="{{ $category->name }}" 
                     class="rounded me-3" 
                     style="width: 80px; height: 80px; object-fit: cover;">
            @endif
            <div>
                <h1 class="h2 mb-1">{{ $category->name }}</h1>
                @if($category->parent)
                    <p class="text-muted mb-0">
                        <small>{{ translate('Subcategory of') }}: {{ $category->parent->name }}</small>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Subcategories (if any) -->
    @php
        $subcategories = \Modules\BeautyBooking\Entities\BeautyServiceCategory::where('parent_id', $category->id)
            ->where('status', 1)
            ->get();
    @endphp
    @if($subcategories->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('Subcategories') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($subcategories as $subcategory)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('beauty-booking.category.show', $subcategory->id) }}" 
                           class="btn btn-outline-primary w-100 text-left">
                            @if($subcategory->image)
                                <img src="{{ asset('storage/app/public/' . $subcategory->image) }}" 
                                     alt="{{ $subcategory->name }}" 
                                     class="rounded me-2" 
                                     style="width: 30px; height: 30px; object-fit: cover;">
                            @endif
                            {{ $subcategory->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Salon List -->
    <div class="mb-3">
        <h5>{{ translate('Salons Offering This Service') }} ({{ $salons->total() }})</h5>
    </div>

    <div class="row g-4">
        @forelse($salons as $salon)
            <div class="col-md-6 col-lg-4">
                <div class="card salon-card h-100">
                    @if($salon->store->image)
                        <img src="{{ asset('storage/app/public/' . $salon->store->image) }}" 
                             class="card-img-top" 
                             alt="{{ $salon->store->name }}" 
                             style="height: 200px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $salon->store->name ?? '' }}</h5>
                            @if(!empty($salon->badges_list))
                                <div>
                                    @foreach($salon->badges_list as $badge)
                                        <span class="badge badge-success">{{ ucfirst(str_replace('_', ' ', $badge)) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="tio-location"></i> {{ $salon->store->address ?? '' }}
                        </p>
                        <div class="d-flex align-items-center mb-3">
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="tio-star{{ $i <= round($salon->avg_rating) ? '' : '-outlined' }}"></i>
                                @endfor
                            </span>
                            <span class="ms-2">{{ number_format($salon->avg_rating, 1) }} ({{ $salon->total_reviews }} {{ translate('reviews') }})</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">{{ $salon->total_bookings }} {{ translate('bookings') }}</span>
                            <a href="{{ route('beauty-booking.salon.show', $salon->id) }}" class="btn btn-sm btn-primary">
                                {{ translate('View Details') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="tio-info"></i> {{ translate('No salons found in this category.') }}
                </div>
            </div>
        @endforelse
    </div>

    @if(isset($salons) && method_exists($salons, 'links'))
        <div class="mt-4">
            {{ $salons->links() }}
        </div>
    @endif
</div>
@endsection

