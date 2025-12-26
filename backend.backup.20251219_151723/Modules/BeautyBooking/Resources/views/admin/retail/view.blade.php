@extends('layouts.admin.app')

@section('title', translate('Product Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/beauty/retail.png') }}" class="w--20" alt="">
                        </span>
                        <span>
                            {{ translate('messages.Product_Details') }}
                        </span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.beautybooking.retail.list') }}" class="btn btn-secondary">
                        <i class="tio-back"></i> {{ translate('messages.Back') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-lg-8">
                <!-- Product Details Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Product_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Name') }}:</strong>
                                    <p class="mt-1">{{ $product->name }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Salon') }}:</strong>
                                    <p class="mt-1">
                                        @if($product->salon)
                                        <a href="{{ route('admin.beautybooking.salon.view', $product->salon_id) }}">
                                            {{ $product->salon->store->name ?? 'N/A' }}
                                        </a>
                                        @else
                                        N/A
                                        @endif
                                    </p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Category') }}:</strong>
                                    <p class="mt-1">{{ $product->category ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Price') }}:</strong>
                                    <p class="mt-1 fs-18 text-success">{{ \App\CentralLogics\Helpers::format_currency($product->price) }}</p>
                                </div>
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Stock_Quantity') }}:</strong>
                                    <span class="badge badge-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }} ml-2">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </div>
                                @if($product->min_stock_level)
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Min_Stock_Level') }}:</strong>
                                    <p class="mt-1">{{ $product->min_stock_level }}</p>
                                </div>
                                @endif
                                <div class="mb-2">
                                    <strong>{{ translate('messages.Status') }}:</strong>
                                    <span class="badge badge-{{ $product->status ? 'success' : 'danger' }} ml-2">
                                        {{ $product->status ? translate('messages.Active') : translate('messages.Inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($product->description)
                        <hr>
                        <div class="mb-3">
                            <strong>{{ translate('messages.Description') }}:</strong>
                            <p class="mt-2">{{ $product->description }}</p>
                        </div>
                        @endif

                        @if($product->image)
                        <hr>
                        <div class="mb-3">
                            <strong>{{ translate('messages.Image') }}:</strong>
                            <div class="mt-2">
                                <img src="{{ asset('storage/app/public/' . $product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img--vertical-2 rounded-10" 
                                     style="max-width: 300px; max-height: 300px;">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Salon Information Card -->
                @if($product->salon)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center mb-3">
                            <div class="avatar avatar-lg mr-3">
                                <img class="img-fluid rounded-circle" 
                                     src="{{ $product->salon->store->logo ?? asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                     alt="Salon">
                            </div>
                            <div class="media-body">
                                <h6 class="mb-0">{{ $product->salon->store->name ?? 'N/A' }}</h6>
                                <span class="text-muted small">{{ $product->salon->store->address ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.beautybooking.salon.view', $product->salon_id) }}" class="btn btn--primary btn-block">
                            <i class="tio-visible"></i> {{ translate('messages.View_Salon') }}
                        </a>
                    </div>
                </div>
                @endif

                <!-- Statistics Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('messages.Statistics') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text--title">{{ translate('messages.Total_Orders') }}:</strong>
                            <p class="mt-1">{{ $totalOrders ?? 0 }}</p>
                        </div>
                        <div class="mb-3">
                            <strong class="text--title">{{ translate('messages.In_Stock') }}:</strong>
                            <span class="badge badge-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }} ml-2">
                                {{ $product->stock_quantity > 0 ? translate('messages.Yes') : translate('messages.No') }}
                            </span>
                        </div>
                        @if($product->min_stock_level && $product->stock_quantity <= $product->min_stock_level)
                        <div class="mb-3">
                            <span class="badge badge-warning">{{ translate('messages.Low_Stock') }}</span>
                        </div>
                        @endif
                        <div class="mb-3">
                            <strong class="text--title">{{ translate('messages.Created_At') }}:</strong>
                            <p class="mt-1">{{ $product->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="mb-0">
                            <strong class="text--title">{{ translate('messages.Updated_At') }}:</strong>
                            <p class="mt-1">{{ $product->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

