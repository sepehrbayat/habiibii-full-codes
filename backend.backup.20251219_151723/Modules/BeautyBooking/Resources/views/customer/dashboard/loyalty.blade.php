@extends('layouts.app')

@section('title', translate('Loyalty Points'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('Loyalty Points') }}</h1>

    <!-- Points Balance -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h2 class="text-primary">{{ number_format($totalPoints, 0) }}</h2>
            <p class="text-muted mb-0">{{ translate('Total Points') }}</p>
        </div>
    </div>

    <!-- Points History -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('Points History') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Points') }}</th>
                            <th>{{ translate('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointsHistory as $point)
                            <tr>
                                <td>{{ $point->created_at->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($point->type ?? 'earned') }}</td>
                                <td class="{{ $point->points > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $point->points > 0 ? '+' : '' }}{{ number_format($point->points, 0) }}
                                </td>
                                <td>{{ $point->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">{{ translate('No points history found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pointsHistory->links() }}
        </div>
    </div>
</div>
@endsection

