@extends('layouts.app')

@section('title', translate('Book Appointment'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="h2 mb-4">{{ translate('Book Appointment') }} - {{ $salon->store->name ?? '' }}</h1>

            <!-- Booking Wizard Steps -->
            <div class="card mb-4">
                <div class="card-body">
                    <ul class="nav nav-pills nav-justified mb-4" id="bookingSteps" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="step1-tab" data-toggle="tab" data-target="#step1" type="button" role="tab">
                                1. {{ translate('Service') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="step2-tab" data-toggle="tab" data-target="#step2" type="button" role="tab">
                                2. {{ translate('Staff') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="step3-tab" data-toggle="tab" data-target="#step3" type="button" role="tab">
                                3. {{ translate('Date & Time') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="step4-tab" data-toggle="tab" data-target="#step4" type="button" role="tab">
                                4. {{ translate('Payment') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="step5-tab" data-toggle="tab" data-target="#step5" type="button" role="tab">
                                5. {{ translate('Confirm') }}
                            </button>
                        </li>
                    </ul>

                    <form id="bookingForm" action="{{ route('beauty-booking.booking.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="salon_id" value="{{ $salon->id }}">

                        <!-- Step 1: Service Selection -->
                        <div class="tab-content" id="bookingStepsContent">
                            <div class="tab-pane fade show active" id="step1" role="tabpanel">
                                <h5 class="mb-3">{{ translate('Select Service') }}</h5>
                                <div class="list-group">
                                    @foreach($services as $service)
                                        <label class="list-group-item">
                                            <input type="radio" name="service_id" value="{{ $service->id }}" required>
                                            <div class="ms-3">
                                                <strong>{{ $service->name }}</strong>
                                                <div class="small text-muted">
                                                    {{ $service->category->name ?? '' }} • {{ $service->duration_minutes }} {{ translate('minutes') }} • {{ number_format($service->price, 2) }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-primary mt-3" onclick="nextStep(2)">{{ translate('Next') }}</button>
                            </div>

                            <!-- Step 2: Staff Selection -->
                            <div class="tab-pane fade" id="step2" role="tabpanel">
                                <h5 class="mb-3">{{ translate('Select Staff') }} <small class="text-muted">({{ translate('Optional') }})</small></h5>
                                <div id="staff-selection-container">
                                    <p class="text-center text-muted">{{ translate('Loading staff members...') }}</p>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="previousStep(1)">{{ translate('Previous') }}</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">{{ translate('Next') }}</button>
                                </div>
                            </div>

                            <!-- Step 3: Date & Time Selection -->
                            <div class="tab-pane fade" id="step3" role="tabpanel">
                                <h5 class="mb-3">{{ translate('Select Date & Time') }}</h5>
                                <div class="form-group mb-3">
                                    <label>{{ translate('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>{{ translate('Available Time Slots') }} <span class="text-danger">*</span></label>
                                    <div id="time-slots-container">
                                        <p class="text-muted">{{ translate('Please select a date first') }}</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="previousStep(2)">{{ translate('Previous') }}</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">{{ translate('Next') }}</button>
                                </div>
                            </div>

                            <!-- Step 4: Payment Method -->
                            <div class="tab-pane fade" id="step4" role="tabpanel">
                                <h5 class="mb-3">{{ translate('Payment Method') }}</h5>
                                <div id="payment-summary-container">
                                    <p class="text-center text-muted">{{ translate('Loading payment summary...') }}</p>
                                </div>
                                <div class="form-group mt-3">
                                    <label>{{ translate('Select Payment Method') }} <span class="text-danger">*</span></label>
                                    <div class="list-group">
                                        <label class="list-group-item">
                                            <input type="radio" name="payment_method" value="digital_payment" required>
                                            <div class="ms-3">
                                                <strong>{{ translate('Online Payment') }}</strong>
                                                <div class="small text-muted">{{ translate('Pay securely online') }}</div>
                                            </div>
                                        </label>
                                        <label class="list-group-item">
                                            <input type="radio" name="payment_method" value="wallet" required>
                                            <div class="ms-3">
                                                <strong>{{ translate('Wallet') }}</strong>
                                                <div class="small text-muted">{{ translate('Use wallet balance') }}</div>
                                            </div>
                                        </label>
                                        <label class="list-group-item">
                                            <input type="radio" name="payment_method" value="cash_payment" required>
                                            <div class="ms-3">
                                                <strong>{{ translate('Cash on Arrival') }}</strong>
                                                <div class="small text-muted">{{ translate('Pay at the salon') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="previousStep(3)">{{ translate('Previous') }}</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(5)">{{ translate('Next') }}</button>
                                </div>
                            </div>

                            <!-- Step 5: Review & Confirm -->
                            <div class="tab-pane fade" id="step5" role="tabpanel">
                                <h5 class="mb-3">{{ translate('Review & Confirm') }}</h5>
                                <div id="review-summary-container">
                                    <p class="text-center text-muted">{{ translate('Loading booking summary...') }}</p>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="previousStep(4)">{{ translate('Previous') }}</button>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="tio-checkmark"></i> {{ translate('Confirm Booking') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    "use strict";
    
    let bookingData = {
        salon_id: {{ $salon->id }},
        service_id: null,
        staff_id: null,
        booking_date: null,
        booking_time: null,
        payment_method: null
    };

    function nextStep(step) {
        // Validate current step before proceeding
        if (step === 2) {
            const serviceId = document.querySelector('input[name="service_id"]:checked');
            if (!serviceId) {
                alert('{{ translate("Please select a service") }}');
                return;
            }
            bookingData.service_id = serviceId.value;
            saveBookingDataToForm();
            loadStaffSelection();
        } else if (step === 3) {
            if (!bookingData.service_id) {
                alert('{{ translate("Please select a service first") }}');
                return;
            }
            const staffId = document.querySelector('input[name="staff_id"]:checked');
            if (staffId) {
                bookingData.staff_id = staffId.value;
            }
            saveBookingDataToForm();
        } else if (step === 4) {
            const date = document.getElementById('booking_date').value;
            const time = document.querySelector('input[name="booking_time"]:checked');
            if (!date || !time) {
                alert('{{ translate("Please select date and time") }}');
                return;
            }
            bookingData.booking_date = date;
            bookingData.booking_time = time.value;
            saveBookingDataToForm();
        } else if (step === 5) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                alert('{{ translate("Please select a payment method") }}');
                return;
            }
            bookingData.payment_method = paymentMethod.value;
            saveBookingDataToForm();
        }

        // Switch tabs FIRST before loading content
        // تعویض تب‌ها ابتدا قبل از بارگذاری محتوا
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
            // Hide all content when switching tabs to prevent content from previous steps showing
            // مخفی کردن تمام محتوا هنگام تعویض تب برای جلوگیری از نمایش محتوای مراحل قبلی
            pane.style.display = 'none';
        });
        
        // Show the target step
        // نمایش مرحله هدف
        const targetTab = document.getElementById('step' + step + '-tab');
        const targetPane = document.getElementById('step' + step);
        
        if (targetTab) {
            targetTab.classList.add('active');
        }
        if (targetPane) {
            targetPane.classList.add('show', 'active');
            targetPane.style.display = 'block';
        }
        
        // Load step-specific content AFTER tab is visible
        // بارگذاری محتوای خاص مرحله بعد از نمایش تب
        if (step === 2) {
            // Load staff selection after tab is visible
            // بارگذاری انتخاب کارکنان بعد از نمایش تب
            setTimeout(() => {
                loadStaffSelection();
            }, 50);
        } else if (step === 4) {
            // Load payment summary after tab is visible
            // بارگذاری خلاصه پرداخت بعد از نمایش تب
            setTimeout(() => {
                loadPaymentSummary(bookingData.booking_date, bookingData.booking_time);
            }, 50);
        } else if (step === 5) {
            // Ensure all data is saved to form before showing review
            // اطمینان از ذخیره تمام داده‌ها در فرم قبل از نمایش بررسی
            saveBookingDataToForm();
            
            // Also read from visible form fields and update bookingData
            // همچنین خواندن از فیلدهای قابل مشاهده فرم و به‌روزرسانی bookingData
            const form = document.getElementById('bookingForm');
            const serviceId = form.querySelector('input[name="service_id"]:checked')?.value;
            const bookingDate = document.getElementById('booking_date')?.value;
            const bookingTime = form.querySelector('input[name="booking_time"]:checked')?.value;
            const paymentMethod = form.querySelector('input[name="payment_method"]:checked')?.value;
            
            if (serviceId) bookingData.service_id = serviceId;
            if (bookingDate) bookingData.booking_date = bookingDate;
            if (bookingTime) bookingData.booking_time = bookingTime;
            if (paymentMethod) bookingData.payment_method = paymentMethod;
            
            // Save again to ensure form has all data
            // ذخیره مجدد برای اطمینان از وجود تمام داده‌ها در فرم
            saveBookingDataToForm();
            
            // Load review summary after tab is visible
            // بارگذاری خلاصه بررسی بعد از نمایش تب
            setTimeout(() => {
                loadReviewSummary();
            }, 50);
        }
    }

    function saveBookingDataToForm() {
        // Save booking data to hidden form fields
        const form = document.getElementById('bookingForm');
        Object.keys(bookingData).forEach(key => {
            let input = form.querySelector(`input[name="${key}"]`);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                form.appendChild(input);
            }
            input.value = bookingData[key] || '';
        });
    }

    function previousStep(step) {
        // Switch tabs FIRST
        // تعویض تب‌ها ابتدا
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
            pane.style.display = 'none';
        });
        
        // Show the target step
        // نمایش مرحله هدف
        const targetTab = document.getElementById('step' + step + '-tab');
        const targetPane = document.getElementById('step' + step);
        
        if (targetTab) {
            targetTab.classList.add('active');
        }
        if (targetPane) {
            targetPane.classList.add('show', 'active');
            targetPane.style.display = 'block';
        }
        
        // Reload step-specific content AFTER tab is visible
        // بارگذاری مجدد محتوای خاص مرحله بعد از نمایش تب
        if (step === 2) {
            // Reload staff selection when going back to step 2
            // بارگذاری مجدد انتخاب کارکنان هنگام بازگشت به مرحله 2
            setTimeout(() => {
                loadStaffSelection();
            }, 50);
        } else if (step === 3) {
            // Reload time slots if date is already selected
            // بارگذاری مجدد بازه‌های زمانی اگر تاریخ قبلاً انتخاب شده است
            const date = document.getElementById('booking_date')?.value;
            if (date) {
                setTimeout(() => {
                    const container = document.getElementById('time-slots-container');
                    container.innerHTML = '<p class="text-center text-muted">{{ translate("Loading available time slots...") }}</p>';
                    
                    fetch('{{ route("beauty-booking.booking.step", ["step" => 3]) }}?date=' + date, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                    })
                    .catch(error => {
                        container.innerHTML = '<p class="text-danger">{{ translate("Error loading time slots") }}</p>';
                    });
                }, 50);
            }
        } else if (step === 4) {
            // Reload payment summary when going back to step 4
            // بارگذاری مجدد خلاصه پرداخت هنگام بازگشت به مرحله 4
            setTimeout(() => {
                const date = bookingData.booking_date || document.getElementById('booking_date')?.value;
                const time = bookingData.booking_time || document.querySelector('input[name="booking_time"]:checked')?.value;
                loadPaymentSummary(date, time);
            }, 50);
        } else if (step === 5) {
            // Reload review summary when going back to step 5
            // بارگذاری مجدد خلاصه بررسی هنگام بازگشت به مرحله 5
            setTimeout(() => {
                loadReviewSummary();
            }, 50);
        }
    }

    function loadStaffSelection() {
        const container = document.getElementById('staff-selection-container');
        container.innerHTML = '<p class="text-center text-muted">{{ translate("Loading...") }}</p>';
        
        // Get service_id from form or bookingData
        // دریافت service_id از فرم یا bookingData
        const serviceId = bookingData.service_id || document.querySelector('input[name="service_id"]:checked')?.value;
        
        if (!serviceId) {
            container.innerHTML = '<p class="text-danger">{{ translate("Please select a service first") }}</p>';
            return;
        }
        
        // Load step 2 content via AJAX with service_id parameter
        // بارگذاری محتوای مرحله 2 از طریق AJAX با پارامتر service_id
        const url = '{{ route("beauty-booking.booking.step", ["step" => 2]) }}' + '?service_id=' + encodeURIComponent(serviceId);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || '{{ translate("Error loading staff selection") }}');
                });
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<p class="text-danger">' + error.message + '</p>';
        });
    }

    // Load time slots when date changes
    document.getElementById('booking_date').addEventListener('change', function() {
        const date = this.value;
        if (!date) return;
        
        const container = document.getElementById('time-slots-container');
        container.innerHTML = '<p class="text-center text-muted">{{ translate("Loading available time slots...") }}</p>';
        
        fetch('{{ route("beauty-booking.booking.step", ["step" => 3]) }}?date=' + date, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<p class="text-danger">{{ translate("Error loading time slots") }}</p>';
        });
    });

    function loadPaymentSummary(bookingDate = null, bookingTime = null) {
        const container = document.getElementById('payment-summary-container');
        if (!container) {
            console.error('Payment summary container not found');
            return;
        }
        
        container.innerHTML = '<p class="text-center text-muted">{{ translate("Loading...") }}</p>';
        
        // Get booking data - use parameters first, then bookingData object, then form fields
        // دریافت داده‌های رزرو - اول پارامترها، سپس bookingData، سپس فیلدهای فرم
        bookingDate = bookingDate || bookingData.booking_date;
        bookingTime = bookingTime || bookingData.booking_time;
        let serviceId = bookingData.service_id;
        
        // If not available, try to get from form (even if tab is hidden)
        // اگر در دسترس نیست، از فرم دریافت کن (حتی اگر تب مخفی باشد)
        if (!bookingDate) {
            const dateInput = document.getElementById('booking_date');
            if (dateInput) bookingDate = dateInput.value;
        }
        
        if (!bookingTime) {
            const timeInput = document.querySelector('input[name="booking_time"]:checked');
            if (timeInput) bookingTime = timeInput.value;
        }
        
        if (!serviceId) {
            const serviceInput = document.querySelector('input[name="service_id"]:checked');
            if (serviceInput) serviceId = serviceInput.value;
        }
        
        // Validate required data
        // اعتبارسنجی داده‌های مورد نیاز
        if (!bookingDate || !bookingTime) {
            container.innerHTML = '<p class="text-danger">{{ translate("Please select date and time first") }}</p>';
            return;
        }
        
        // Build URL with parameters
        // ساخت URL با پارامترها
        let url = '{{ route("beauty-booking.booking.step", ["step" => 4]) }}';
        const params = new URLSearchParams();
        params.append('date', bookingDate);
        params.append('time', bookingTime);
        if (serviceId) params.append('service_id', serviceId);
        url += '?' + params.toString();
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(async response => {
            if (!response.ok) {
                // Try to parse as JSON first, but handle HTML error pages
                // تلاش برای تجزیه به عنوان JSON ابتدا، اما مدیریت صفحات خطای HTML
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    throw new Error(data.error || '{{ translate("Error loading payment summary") }}');
                } else {
                    // Server returned HTML error page (500, 404, etc.)
                    // سرور صفحه خطای HTML برگردانده است (500، 404، و غیره)
                    throw new Error('{{ translate("Server error. Please try again or contact support.") }}');
                }
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading payment summary:', error);
            container.innerHTML = '<p class="text-danger">' + error.message + '</p>';
        });
    }

    function loadReviewSummary() {
        const container = document.getElementById('review-summary-container');
        container.innerHTML = '<p class="text-center text-muted">{{ translate("Loading...") }}</p>';
        
        fetch('{{ route("beauty-booking.booking.step", ["step" => 5]) }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<p class="text-danger">{{ translate("Error loading review summary") }}</p>';
        });
    }

    // Store booking data in form before submit
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        // First, ensure all current form values are saved to bookingData
        // ابتدا، اطمینان از ذخیره تمام مقادیر فعلی فرم در bookingData
        const form = document.getElementById('bookingForm');
        
        // Read from visible/hidden fields and update bookingData
        // خواندن از فیلدهای قابل مشاهده/مخفی و به‌روزرسانی bookingData
        const serviceId = form.querySelector('input[name="service_id"]:checked')?.value || form.querySelector('input[name="service_id"]')?.value;
        const bookingDate = form.querySelector('input[name="booking_date"]')?.value || document.getElementById('booking_date')?.value;
        const bookingTime = form.querySelector('input[name="booking_time"]:checked')?.value || form.querySelector('input[name="booking_time"]')?.value;
        
        // For payment_method, check both checked radio buttons (even if in hidden tab) and hidden field
        // برای payment_method، بررسی هم رادیو دکمه‌های انتخاب شده (حتی اگر در تب مخفی باشند) و هم فیلد مخفی
        let paymentMethod = null;
        // Try to find checked radio button (works even if tab is hidden)
        // تلاش برای یافتن رادیو دکمه انتخاب شده (حتی اگر تب مخفی باشد کار می‌کند)
        const checkedPaymentRadio = form.querySelector('input[name="payment_method"]:checked');
        if (checkedPaymentRadio) {
            paymentMethod = checkedPaymentRadio.value;
        } else {
            // Fallback to hidden field created by saveBookingDataToForm()
            // استفاده از فیلد مخفی ایجاد شده توسط saveBookingDataToForm()
            const hiddenPaymentField = form.querySelector('input[name="payment_method"][type="hidden"]');
            if (hiddenPaymentField && hiddenPaymentField.value) {
                paymentMethod = hiddenPaymentField.value;
            }
        }
        
        // Update bookingData with current form values
        // به‌روزرسانی bookingData با مقادیر فعلی فرم
        if (serviceId) bookingData.service_id = serviceId;
        if (bookingDate) bookingData.booking_date = bookingDate;
        if (bookingTime) bookingData.booking_time = bookingTime;
        if (paymentMethod) bookingData.payment_method = paymentMethod;
        
        // Save all data to hidden form fields
        // ذخیره تمام داده‌ها در فیلدهای مخفی فرم
        saveBookingDataToForm();
        
        // Validate all required fields
        // اعتبارسنجی تمام فیلدهای مورد نیاز
        if (!bookingData.service_id || !bookingData.booking_date || !bookingData.booking_time || !bookingData.payment_method) {
            e.preventDefault();
            
            // Show specific missing field message
            // نمایش پیام فیلد گم‌شده خاص
            let missingFields = [];
            if (!bookingData.service_id) missingFields.push('{{ translate("Service") }}');
            if (!bookingData.booking_date) missingFields.push('{{ translate("Date") }}');
            if (!bookingData.booking_time) missingFields.push('{{ translate("Time") }}');
            if (!bookingData.payment_method) missingFields.push('{{ translate("Payment Method") }}');
            
            alert('{{ translate("Please complete all booking steps") }}: ' + missingFields.join(', '));
            return false;
        }
    });
</script>
@endpush



