@extends('layouts.app')

@section('title', translate('Wallet Transactions'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('Wallet Transactions') }}</h1>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Reference') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type ?? '')) }}</td>
                                <td class="{{ $transaction->debit > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $transaction->debit > 0 ? '-' : '+' }}{{ number_format(abs($transaction->amount), 2) }}
                                </td>
                                <td>{{ $transaction->reference ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">{{ translate('No transactions found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection

