@extends('layouts.app')

@section('title', $staff->name ?? translate('Staff Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('beauty-booking.salon.show', $staff->salon_id) }}" class="btn btn-outline-secondary">
                    <i class="tio-back"></i> {{ translate('Back to Salon') }}
                </a>
            </div>

            <!-- Staff Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            @if($staff->avatar)
                                <img src="{{ asset('storage/app/public/' . $staff->avatar) }}" 
                                     alt="{{ $staff->name }}" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="tio-user" style="font-size: 60px; color: #6c757d;"></i>
                                </div>
                            @endif
                            <h4 class="mb-1">{{ $staff->name }}</h4>
                            @if($staff->status)
                                <span class="badge badge-success">{{ translate('Available') }}</span>
                            @else
                                <span class="badge badge-danger">{{ translate('Unavailable') }}</span>
                            @endif
                        </div>
                        <div class="col-md-8">
                            @if($staff->email)
                                <div class="mb-3">
                                    <strong>{{ translate('Email') }}:</strong>
                                    <a href="mailto:{{ $staff->email }}">{{ $staff->email }}</a>
                                </div>
                            @endif
                            
                            @if($staff->phone)
                                <div class="mb-3">
                                    <strong>{{ translate('Phone') }}:</strong>
                                    <a href="tel:{{ $staff->phone }}">{{ $staff->phone }}</a>
                                </div>
                            @endif

                            @if($staff->specializations && count($staff->specializations) > 0)
                                <div class="mb-3">
                                    <strong>{{ translate('Specializations') }}:</strong>
                                    <div class="mt-2">
                                        @foreach($staff->specializations as $spec)
                                            <span class="badge badge-primary me-1">{{ $spec }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($staff->salon)
                                <div class="mb-3">
                                    <strong>{{ translate('Salon') }}:</strong>
                                    <a href="{{ route('beauty-booking.salon.show', $staff->salon_id) }}">
                                        {{ $staff->salon->store->name ?? '' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Working Hours -->
            @if($staff->working_hours && count($staff->working_hours) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Working Hours') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ translate('Day') }}</th>
                                    <th>{{ translate('Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = [
                                        'monday' => translate('Monday'),
                                        'tuesday' => translate('Tuesday'),
                                        'wednesday' => translate('Wednesday'),
                                        'thursday' => translate('Thursday'),
                                        'friday' => translate('Friday'),
                                        'saturday' => translate('Saturday'),
                                        'sunday' => translate('Sunday')
                                    ];
                                @endphp
                                @foreach($days as $dayKey => $dayName)
                                    <tr>
                                        <td><strong>{{ $dayName }}</strong></td>
                                        <td>
                                            @if(isset($staff->working_hours[$dayKey]) && 
                                                isset($staff->working_hours[$dayKey]['open']) && 
                                                isset($staff->working_hours[$dayKey]['close']))
                                                {{ $staff->working_hours[$dayKey]['open'] }} - {{ $staff->working_hours[$dayKey]['close'] }}
                                            @else
                                                <span class="text-muted">{{ translate('Closed') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Reviews -->
            @if($staff->reviews && $staff->reviews->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Reviews') }} ({{ $staff->reviews->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($staff->reviews->take(5) as $review)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>{{ ($review->user->f_name ?? '') . ' ' . ($review->user->l_name ?? '') }}</strong>
                                <span class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="tio-star{{ $i <= $review->rating ? '' : '-outlined' }}"></i>
                                    @endfor
                                </span>
                            </div>
                            @if($review->comment)
                                <p class="mb-1">{{ $review->comment }}</p>
                            @endif
                            <small class="text-muted">{{ $review->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Book Appointment Button -->
            @auth('customer')
                @if($staff->status && $staff->salon)
                    <div class="text-center mt-4">
                        <a href="{{ route('beauty-booking.booking.create', $staff->salon_id) }}" class="btn btn-primary btn-lg">
                            <i class="tio-calendar"></i> {{ translate('Book Appointment') }}
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        {{ translate('Login to Book') }}
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection

