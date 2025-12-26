@extends('layouts.app')

@section('title', translate('My Reviews'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('My Reviews') }}</h1>

    <div class="row g-4">
        @forelse($reviews as $review)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0">{{ $review->salon->store->name ?? '' }}</h6>
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="tio-star{{ $i <= $review->rating ? '' : '-outlined' }}"></i>
                                @endfor
                            </span>
                        </div>
                        <p class="mb-2">{{ $review->comment }}</p>
                        <small class="text-muted">{{ $review->created_at->format('Y-m-d') }}</small>
                        <div class="mt-2">
                            @if($review->status == 'pending')
                                <span class="badge badge-warning">{{ translate('Pending Approval') }}</span>
                            @elseif($review->status == 'approved')
                                <span class="badge badge-success">{{ translate('Approved') }}</span>
                            @else
                                <span class="badge badge-danger">{{ translate('Rejected') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    {{ translate('No reviews found') }}
                </div>
            </div>
        @endforelse
    </div>

    {{ $reviews->links() }}
</div>
@endsection

