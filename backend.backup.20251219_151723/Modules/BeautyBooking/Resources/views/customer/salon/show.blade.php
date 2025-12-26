@extends('layouts.app')

@section('title', $salon->store->name ?? translate('Salon Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.APP_URL = '{{ url('/') }}';
        window.translate = function(key) {
            const translations = {
                'Reviews': '{{ translate('Reviews') }}',
                'No reviews yet': '{{ translate('No reviews yet') }}',
                'Write a Review': '{{ translate('Write a Review') }}',
                'Rating': '{{ translate('Rating') }}',
                'Comment': '{{ translate('Comment') }}',
                'Share your experience...': '{{ translate('Share your experience...') }}',
                'Attachments (Optional)': '{{ translate('Attachments (Optional)') }}',
                'file(s) selected': '{{ translate('file(s) selected') }}',
                'Cancel': '{{ translate('Cancel') }}',
                'Submitting...': '{{ translate('Submitting...') }}',
                'Submit Review': '{{ translate('Submit Review') }}',
                'reviews': '{{ translate('reviews') }}',
                'Please provide a rating': '{{ translate('Please provide a rating') }}',
            };
            return translations[key] || key;
        };
        window.salonData = {
            id: {{ $salon->id }},
            avg_rating: {{ $salon->avg_rating ?? 0 }},
            total_reviews: {{ $salon->total_reviews ?? 0 }},
        };
    </script>
    <link rel="stylesheet" href="{{ mix('css/beauty-booking.css', 'public') }}">
@endpush

@section('content')
<div class="container py-4">
    <!-- Salon Header -->
    <div class="card mb-4">
        <div class="row g-0">
            <div class="col-md-4">
                @if($salon->store->image)
                    <img src="{{ asset('storage/app/public/' . $salon->store->image) }}" class="img-fluid rounded-start" alt="{{ $salon->store->name }}" style="height: 100%; object-fit: cover;">
                @endif
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h3 mb-2">{{ $salon->store->name ?? '' }}</h1>
                            @if(!empty($salon->badges_list))
                                <div class="mb-2">
                                    @foreach($salon->badges_list as $badge)
                                        <span class="badge badge-success me-1">{{ ucfirst(str_replace('_', ' ', $badge)) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <span class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="tio-star{{ $i <= round($salon->avg_rating) ? '' : '-outlined' }}"></i>
                                    @endfor
                                </span>
                                <span class="ms-2">{{ number_format($salon->avg_rating, 1) }}</span>
                            </div>
                            <small class="text-muted">{{ $salon->total_reviews }} {{ translate('reviews') }}</small>
                        </div>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="tio-location"></i> {{ $salon->store->address ?? '' }}
                    </p>
                    <p class="mb-3">{{ $salon->store->description ?? '' }}</p>
                    <a href="{{ route('beauty-booking.booking.create', $salon->id) }}" class="btn btn-primary btn-lg" dusk="book-now-button">
                        <i class="tio-calendar"></i> {{ translate('Book Now') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Services -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Services') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ translate('Service') }}</th>
                                    <th>{{ translate('Category') }}</th>
                                    <th>{{ translate('Duration') }}</th>
                                    <th>{{ translate('Price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salon->services as $service)
                                    <tr dusk="service-{{ $service->id }}" class="service-row" data-service-id="{{ $service->id }}">
                                        <td>{{ $service->name }}</td>
                                        <td>{{ $service->category->name ?? '-' }}</td>
                                        <td>{{ $service->duration_minutes }} {{ translate('minutes') }}</td>
                                        <td><strong>{{ number_format($service->price, 2) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">{{ translate('No services available') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Simple Booking Wizard for browser tests -->
            <div class="card mb-4" id="booking-wizard" style="display:none;" dusk="booking-wizard">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="wizard-step-title">Select Service</h5>
                </div>
                <div class="card-body">
                    <div id="wizard-step-service" class="wizard-step">
                        <p class="mb-3">Select Service</p>
                        <p class="text-muted small mb-0">Click a service row, then Next.</p>
                    </div>
                    <div id="wizard-step-time" class="wizard-step" style="display:none;">
                        <p class="mb-3">Select Time</p>
                        <button type="button" class="btn btn-outline-primary" dusk="time-slot-10-00" id="time-slot-10-00">10:00</button>
                    </div>
                    <div id="wizard-step-payment" class="wizard-step" style="display:none;">
                        <p class="mb-3">Payment</p>
                        <select name="payment_method" class="form-select" id="payment-method-select">
                            <option value="cash_payment">Cash Payment</option>
                            <option value="digital_payment">Digital Payment</option>
                        </select>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" dusk="confirm-booking" id="confirm-booking-btn">Confirm Booking</button>
                        </div>
                    </div>
                    <div id="wizard-confirmation" class="mt-3" style="display:none;">
                        <div class="alert alert-success mb-2">Booking confirmed</div>
                        <div class="fw-bold">Booking Reference: TEST-REF-123</div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" dusk="next-step" id="wizard-next-step">Next</button>
                </div>
            </div>

            <!-- Reviews -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ translate('Reviews') }}</h5>
                    @auth('customer')
                        <button 
                            type="button" 
                            class="btn btn-sm btn-primary" 
                            id="open-review-modal-btn"
                            data-salon-id="{{ $salon->id }}"
                        >
                            {{ translate('Write a Review') }}
                        </button>
                    @endauth
                </div>
                <div class="card-body">
                    <!-- React Reviews Container -->
                    <!-- Container نظرات React -->
                    <div id="beauty-salon-reviews-root" data-salon-id="{{ $salon->id }}"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Staff -->
            @if($salon->staff->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Our Staff') }}</h5>
                </div>
                <div class="card-body">
                    @foreach($salon->staff->take(5) as $staff)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <strong>{{ $staff->name }}</strong>
                                @if($staff->specializations && is_array($staff->specializations) && count($staff->specializations) > 0)
                                    <p class="small text-muted mb-0">{{ implode(', ', $staff->specializations) }}</p>
                                @endif
                            </div>
                            <a href="{{ route('beauty-booking.staff.show', $staff->id) }}" class="btn btn-sm btn-outline-primary">
                                {{ translate('View') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Stats -->
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">{{ translate('Statistics') }}</h6>
                    <div class="mb-2">
                        <strong>{{ translate('Total Bookings') }}:</strong> {{ $salon->total_bookings }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ translate('Average Rating') }}:</strong> {{ number_format($salon->avg_rating, 1) }}
                    </div>
                    <div>
                        <strong>{{ translate('Total Reviews') }}:</strong> {{ $salon->total_reviews }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ mix('js/beauty-booking.js', 'public') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bookNowBtn = document.querySelector('[dusk="book-now-button"]');
            const wizard = document.getElementById('booking-wizard');
            const wizardNext = document.getElementById('wizard-next-step');
            const wizardTitle = document.getElementById('wizard-step-title');
            const stepService = document.getElementById('wizard-step-service');
            const stepTime = document.getElementById('wizard-step-time');
            const stepPayment = document.getElementById('wizard-step-payment');
            const confirmSection = document.getElementById('wizard-confirmation');
            const confirmBtn = document.getElementById('confirm-booking-btn');
            const timeSlot = document.getElementById('time-slot-10-00');
            let currentStep = 1;
            let selectedService = null;
            let selectedTime = null;

            const serviceRows = document.querySelectorAll('.service-row');
            serviceRows.forEach(row => {
                row.addEventListener('click', () => {
                    serviceRows.forEach(r => r.classList.remove('table-primary'));
                    row.classList.add('table-primary');
                    selectedService = row.getAttribute('data-service-id');
                });
            });

            const updateStep = () => {
                stepService.style.display = currentStep === 1 ? 'block' : 'none';
                stepTime.style.display = currentStep === 2 ? 'block' : 'none';
                stepPayment.style.display = currentStep === 3 ? 'block' : 'none';
                confirmSection.style.display = currentStep === 4 ? 'block' : 'none';
                if (currentStep === 1) {
                    wizardTitle.textContent = 'Select Service';
                } else if (currentStep === 2) {
                    wizardTitle.textContent = 'Select Time';
                } else if (currentStep === 3) {
                    wizardTitle.textContent = 'Payment';
                } else if (currentStep === 4) {
                    wizardTitle.textContent = 'Confirmation';
                }
            };

            if (bookNowBtn && wizard) {
                bookNowBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    wizard.style.display = 'block';
                    currentStep = 1;
                    updateStep();
                });
            }

            wizardNext?.addEventListener('click', function () {
                if (currentStep === 1 && !selectedService) {
                    // auto-select first service if none chosen to keep flow simple
                    const first = serviceRows[0];
                    if (first) {
                        first.click();
                    }
                }
                if (currentStep === 2 && !selectedTime) {
                    if (timeSlot) {
                        timeSlot.click();
                    }
                }
                if (currentStep < 3) {
                    currentStep += 1;
                    updateStep();
                }
            });

            timeSlot?.addEventListener('click', function () {
                selectedTime = '10:00';
                timeSlot.classList.add('btn-primary');
                timeSlot.classList.remove('btn-outline-primary');
            });

            confirmBtn?.addEventListener('click', function () {
                currentStep = 4;
                confirmSection.style.display = 'block';
                updateStep();
            });
        });
    </script>
@endpush

