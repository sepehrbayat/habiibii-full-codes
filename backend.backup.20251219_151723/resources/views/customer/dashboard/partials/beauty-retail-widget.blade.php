@if(isset($beautyWidgets['recent_retail_orders']))
<!-- Retail Orders Widget -->
<!-- ویجت سفارشات خرده‌فروشی -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Retail Orders') }}</h5>
        <a href="{{ route('beauty-booking.retail-orders') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        @if($beautyWidgets['recent_retail_orders']->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['recent_retail_orders']->take(5) as $order)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ translate('Order') }} #{{ $order->order_reference }}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    {{ $order->salon->store->name ?? translate('Unknown Salon') }}
                                </p>
                                <p class="mb-0">
                                    <strong>{{ \App\CentralLogics\Helpers::format_currency($order->total_amount ?? 0) }}</strong>
                                </p>
                                <span class="badge badge-{{ ($order->status ?? '') == 'delivered' ? 'success' : (($order->status ?? '') == 'processing' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($order->status ?? 'unknown') }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <a href="{{ route('beauty-booking.retail-orders') }}" class="btn btn-sm btn-primary">
                                    {{ translate('View') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="tio-shopping-cart fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No retail orders') }}</p>
                <a href="{{ route('beauty-booking.search') }}" class="btn btn-sm btn-primary">
                    {{ translate('Browse Products') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

