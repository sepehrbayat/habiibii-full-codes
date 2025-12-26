@extends('layouts.app')

@section('title', translate('Consultations'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('My Consultations') }}</h1>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Salon') }}</th>
                            <th>{{ translate('Service') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $consultation)
                            <tr>
                                <td>{{ $consultation->booking_date->format('Y-m-d') }}</td>
                                <td>{{ $consultation->salon->store->name ?? '' }}</td>
                                <td>{{ $consultation->service->name ?? '' }}</td>
                                <td>
                                    <span class="badge badge-{{ $consultation->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('beauty-booking.my-bookings.show', $consultation->id) }}" class="btn btn-sm btn-primary">
                                        {{ translate('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ translate('No consultations found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $consultations->links() }}
        </div>
    </div>
</div>
@endsection

