@if(isset($beautyWidgets['active_gift_cards']))
<!-- Gift Cards Widget -->
<!-- ویجت کارت‌های هدیه -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Gift Cards') }}</h5>
        <a href="{{ route('beauty-booking.gift-cards') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        @if($beautyWidgets['active_gift_cards']->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['active_gift_cards']->take(5) as $giftCard)
                    @php
                        $daysUntilExpiry = $giftCard->expires_at ? now()->diffInDays($giftCard->expires_at, false) : null;
                    @endphp
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ \App\CentralLogics\Helpers::format_currency($giftCard->amount ?? 0) }}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    {{ translate('Code') }}: <strong>{{ $giftCard->code ?? 'N/A' }}</strong>
                                </p>
                                @if(isset($giftCard->salon))
                                    <p class="mb-0 text-muted small">
                                        {{ $giftCard->salon->store->name ?? ($giftCard->salon->name ?? translate('Unknown Salon')) }}
                                    </p>
                                @endif
                                @if($daysUntilExpiry !== null && $daysUntilExpiry <= 30)
                                    <small class="text-warning">
                                        <i class="tio-warning"></i> {{ translate('Expires in') }} {{ $daysUntilExpiry }} {{ translate('days') }}
                                    </small>
                                @endif
                            </div>
                            <span class="badge badge-success">
                                {{ translate('Active') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(isset($beautyStats['gift_card_balance']))
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ translate('Total Balance') }}:</strong>
                        <strong class="text-success">{{ \App\CentralLogics\Helpers::format_currency($beautyStats['gift_card_balance']) }}</strong>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="tio-gift fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No active gift cards') }}</p>
                <a href="{{ route('beauty-booking.gift-cards') }}" class="btn btn-sm btn-primary">
                    {{ translate('Purchase Gift Card') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

