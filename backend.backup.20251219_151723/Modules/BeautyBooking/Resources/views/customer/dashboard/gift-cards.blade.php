@extends('layouts.app')

@section('title', translate('Gift Cards'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('My Gift Cards') }}</h1>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Code') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Salon') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Expires At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($giftCards as $giftCard)
                            <tr>
                                <td><code>{{ $giftCard->code }}</code></td>
                                <td>{{ number_format($giftCard->amount, 2) }}</td>
                                <td>{{ $giftCard->salon->store->name ?? translate('General') }}</td>
                                <td>
                                    <span class="badge badge-{{ $giftCard->status == 'active' ? 'success' : ($giftCard->status == 'redeemed' ? 'info' : 'danger') }}">
                                        {{ ucfirst($giftCard->status) }}
                                    </span>
                                </td>
                                <td>{{ $giftCard->expires_at ? $giftCard->expires_at->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ translate('No gift cards found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $giftCards->links() }}
        </div>
    </div>
</div>
@endsection

