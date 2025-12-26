<!-- Wallet Integration Widget -->
<!-- ویجت یکپارچه کیف پول -->
@php
    $user = auth('customer')->user();
    // Get wallet transactions related to beauty bookings
    // دریافت تراکنش‌های کیف پول مربوط به رزروهای زیبایی
    $beautyTransactions = \App\Models\WalletTransaction::where('user_id', $user->id)
        ->where(function($q) {
            $q->where('reference', 'like', '%beauty%')
              ->orWhere('reference', 'like', '%booking%');
        })
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    // Calculate beauty-related wallet balance
    // محاسبه موجودی کیف پول مربوط به زیبایی
    $beautyDebit = \App\Models\WalletTransaction::where('user_id', $user->id)
        ->where(function($q) {
            $q->where('reference', 'like', '%beauty%')
              ->orWhere('reference', 'like', '%booking%');
        })
        ->sum('debit');
    $beautyCredit = \App\Models\WalletTransaction::where('user_id', $user->id)
        ->where(function($q) {
            $q->where('reference', 'like', '%beauty%')
              ->orWhere('reference', 'like', '%booking%');
        })
        ->sum('credit');
    $beautyWalletBalance = $beautyDebit - $beautyCredit;
@endphp
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Beauty Wallet') }}</h5>
        <a href="{{ route('beauty-booking.wallet') }}" class="btn btn-sm btn-link">
            {{ translate('View All') }}
        </a>
    </div>
    <div class="card-body">
        <!-- Wallet Balance -->
        <!-- موجودی کیف پول -->
        <div class="text-center mb-4 pb-3 border-bottom">
            <h4 class="text-success mb-1">{{ \App\CentralLogics\Helpers::format_currency(abs($beautyWalletBalance)) }}</h4>
            <p class="text-muted mb-0">{{ translate('Beauty Services Balance') }}</p>
        </div>
        
        <!-- Recent Transactions -->
        <!-- تراکنش‌های اخیر -->
        <h6 class="mb-3">{{ translate('Recent Transactions') }}</h6>
        @if($beautyTransactions->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyTransactions as $transaction)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1">
                                    <strong class="{{ $transaction->debit > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->debit > 0 ? '+' : '-' }}{{ \App\CentralLogics\Helpers::format_currency(abs($transaction->debit > 0 ? $transaction->debit : $transaction->credit)) }}
                                    </strong>
                                </p>
                                <p class="mb-0 text-muted small">
                                    {{ $transaction->transaction_note ?? translate('Beauty booking transaction') }}
                                </p>
                            </div>
                            <small class="text-muted">
                                {{ $transaction->created_at ? $transaction->created_at->format('M d, Y') : 'N/A' }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-3">
                <p class="text-muted">{{ translate('No recent transactions') }}</p>
            </div>
        @endif
    </div>
</div>

