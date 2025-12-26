/**
 * Beauty Booking JavaScript
 * جاوااسکریپت رزرو زیبایی
 *
 * Booking form logic and availability checking
 * منطق فرم رزرو و بررسی دسترسی‌پذیری
 */

(function() {
    'use strict';

    /**
     * Check availability for selected date and time
     * بررسی دسترسی‌پذیری برای تاریخ و زمان انتخاب شده
     *
     * @param {number} salonId
     * @param {number} serviceId
     * @param {string} date
     * @param {number|null} staffId
     * @param {function} callback
     */
    function checkAvailability(salonId, serviceId, date, staffId, callback) {
        const url = '/api/v1/beautybooking/availability/check';
        const data = {
            salon_id: salonId,
            service_id: serviceId,
            date: date,
            staff_id: staffId
        };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + window.authToken
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            if (!response.ok) {
                // Handle HTTP errors
                // مدیریت خطاهای HTTP
                // Read response body once before passing to error handler
                // خواندن body پاسخ یک بار قبل از ارسال به error handler
                let errorData = {};
                try {
                    errorData = await response.json();
                } catch (e) {
                    errorData = {};
                }
                
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                    // Pass pre-parsed errorData to avoid reading response body twice
                    // ارسال errorData از پیش parse شده برای جلوگیری از خواندن body پاسخ دوباره
                    await window.BeautyErrorHandler.showFetchError(response, null, errorData);
                }
                
                if (callback) {
                    callback({ success: false, error: errorData.message || 'Failed to check availability' });
                }
                return;
            }
            return response.json();
        })
        .then(result => {
            if (result && callback) {
                callback(result);
            }
        })
        .catch(async error => {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                await window.BeautyErrorHandler.showFetchError(null, error);
            } else {
                console.error('Error checking availability:', error);
            }
            if (callback) {
                callback({ success: false, error: error.message || 'Network error occurred' });
            }
        });
    }

    /**
     * Load available time slots
     * بارگذاری زمان‌های خالی
     *
     * @param {number} salonId
     * @param {number} serviceId
     * @param {string} date
     * @param {number|null} staffId
     */
    function loadAvailableSlots(salonId, serviceId, date, staffId) {
        checkAvailability(salonId, serviceId, date, staffId, function(result) {
            if (result.data && result.data.available_slots) {
                updateTimeSlotSelect(result.data.available_slots);
            } else {
                showAvailabilityError(result.error || 'No available slots');
            }
        });
    }

    /**
     * Update time slot select dropdown
     * به‌روزرسانی دراپ‌دان زمان‌ها
     *
     * @param {Array} slots
     */
    function updateTimeSlotSelect(slots) {
        const timeSelect = document.getElementById('booking_time');
        if (!timeSelect) {
            return;
        }

        // Clear existing options
        // پاک کردن گزینه‌های موجود
        timeSelect.innerHTML = '<option value="">{{ translate("Select Time") }}</option>';

        // Add available slots
        // افزودن زمان‌های خالی
        slots.forEach(function(slot) {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;
            timeSelect.appendChild(option);
        });

        if (slots.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '{{ translate("No available slots") }}';
            option.disabled = true;
            timeSelect.appendChild(option);
        }
    }

    /**
     * Show availability error message
     * نمایش پیام خطای دسترسی‌پذیری
     *
     * @param {string} message
     */
    function showAvailabilityError(message) {
        // Show error alert or message
        // نمایش هشدار یا پیام خطا
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.textContent = message;
        document.querySelector('.booking-form').prepend(alertDiv);
    }

    /**
     * Show inline error message for a field
     * نمایش پیام خطای درون خطی برای یک فیلد
     *
     * @param {HTMLElement} field Input field element
     * @param {string} message Error message
     */
    function showFieldError(field, message) {
        // Remove existing error
        // حذف خطای موجود
        clearFieldError(field);

        // Add error class to field
        // افزودن کلاس خطا به فیلد
        field.classList.add('is-invalid');

        // Create error message element
        // ایجاد عنصر پیام خطا
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.setAttribute('data-field-error', field.name || field.id);

        // Insert error message after field
        // درج پیام خطا پس از فیلد
        field.parentNode.appendChild(errorDiv);
    }

    /**
     * Clear inline error message for a field
     * پاک کردن پیام خطای درون خطی برای یک فیلد
     *
     * @param {HTMLElement} field Input field element
     */
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const existingError = field.parentNode.querySelector('.invalid-feedback[data-field-error="' + (field.name || field.id) + '"]');
        if (existingError) {
            existingError.remove();
        }
    }

    /**
     * Clear all field errors in form
     * پاک کردن تمام خطاهای فیلد در فرم
     *
     * @param {HTMLFormElement} form
     */
    function clearAllFieldErrors(form) {
        const invalidFields = form.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => clearFieldError(field));
    }

    /**
     * Validate booking form with comprehensive checks
     * اعتبارسنجی فرم رزرو با بررسی‌های جامع
     *
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateBookingForm(form) {
        // Clear previous errors
        // پاک کردن خطاهای قبلی
        clearAllFieldErrors(form);

        let isValid = true;
        const errors = [];

        // Validate salon_id
        // اعتبارسنجی salon_id
        const salonField = form.querySelector('[name="salon_id"]');
        const salonId = salonField ? salonField.value : '';
        if (!salonId || salonId === '' || salonId === '0') {
            if (salonField) {
                showFieldError(salonField, translate('messages.please_select_salon') || 'Please select a salon');
            }
            errors.push('salon_id');
            isValid = false;
        }

        // Validate service_id
        // اعتبارسنجی service_id
        const serviceField = form.querySelector('[name="service_id"]');
        const serviceId = serviceField ? serviceField.value : '';
        if (!serviceId || serviceId === '' || serviceId === '0') {
            if (serviceField) {
                showFieldError(serviceField, translate('messages.please_select_service') || 'Please select a service');
            }
            errors.push('service_id');
            isValid = false;
        }

        // Validate booking_date
        // اعتبارسنجی booking_date
        const dateField = form.querySelector('[name="booking_date"]');
        const bookingDate = dateField ? dateField.value : '';
        if (!bookingDate) {
            if (dateField) {
                showFieldError(dateField, translate('messages.please_select_booking_date') || 'Please select a booking date');
            }
            errors.push('booking_date');
            isValid = false;
        } else {
            // Check date format
            // بررسی فرمت تاریخ
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(bookingDate)) {
                if (dateField) {
                    showFieldError(dateField, translate('messages.invalid_date_format') || 'Invalid date format');
                }
                errors.push('booking_date');
                isValid = false;
            } else {
                // Check if date is not in the past
                // بررسی نبودن تاریخ در گذشته
                const selectedDate = new Date(bookingDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    if (dateField) {
                        showFieldError(dateField, translate('messages.booking_date_cannot_be_in_past') || 'Booking date cannot be in the past');
                    }
                    errors.push('booking_date');
                    isValid = false;
                }
            }
        }

        // Validate booking_time
        // اعتبارسنجی booking_time
        const timeField = form.querySelector('[name="booking_time"]');
        const bookingTime = timeField ? timeField.value : '';
        if (!bookingTime || bookingTime === '') {
            if (timeField) {
                showFieldError(timeField, translate('messages.please_select_booking_time') || 'Please select a booking time');
            }
            errors.push('booking_time');
            isValid = false;
        } else {
            // Validate time format (HH:mm or HH:mm:ss)
            // اعتبارسنجی فرمت زمان
            const timeRegex = /^([0-1][0-9]|2[0-3]):[0-5][0-9](:([0-5][0-9]))?$/;
            if (!timeRegex.test(bookingTime)) {
                if (timeField) {
                    showFieldError(timeField, translate('messages.invalid_time_format') || 'Invalid time format');
                }
                errors.push('booking_time');
                isValid = false;
            } else {
                // Check if selected time is in available slots
                // بررسی اینکه آیا زمان انتخاب شده در لیست زمان‌های خالی است
                const timeSelect = document.getElementById('booking_time');
                if (timeSelect && timeSelect.options.length > 1) {
                    const selectedOption = timeSelect.options[timeSelect.selectedIndex];
                    if (selectedOption.disabled || selectedOption.value === '') {
                        if (timeField) {
                            showFieldError(timeField, translate('messages.selected_time_not_available') || 'Selected time slot is not available');
                        }
                        errors.push('booking_time');
                        isValid = false;
                    }
                }
            }
        }

        // Validate staff_id if provided (optional field)
        // اعتبارسنجی staff_id در صورت ارائه (فیلد اختیاری)
        const staffField = form.querySelector('[name="staff_id"]');
        if (staffField && staffField.value && staffField.value !== '' && staffField.value !== '0') {
            // Staff is selected, validate it's a valid selection
            // کارمند انتخاب شده است، اعتبارسنجی انتخاب معتبر
            const staffId = staffField.value;
            if (isNaN(staffId) || parseInt(staffId) <= 0) {
                if (staffField) {
                    showFieldError(staffField, translate('messages.invalid_staff_selection') || 'Invalid staff selection');
                }
                errors.push('staff_id');
                isValid = false;
            }
        }

        // Validate payment_method if required
        // اعتبارسنجی payment_method در صورت نیاز
        const paymentField = form.querySelector('[name="payment_method"]');
        if (paymentField && paymentField.hasAttribute('required')) {
            const paymentMethod = paymentField.value;
            if (!paymentMethod || paymentMethod === '') {
                if (paymentField) {
                    showFieldError(paymentField, translate('messages.please_select_payment_method') || 'Please select a payment method');
                }
                errors.push('payment_method');
                isValid = false;
            }
        }

        // Scroll to first error if any
        // اسکرول به اولین خطا در صورت وجود
        if (!isValid && errors.length > 0) {
            const firstErrorField = form.querySelector('[name="' + errors[0] + '"]');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
        }

        return isValid;
    }

    /**
     * Submit booking form
     * ارسال فرم رزرو
     *
     * @param {HTMLFormElement} form
     */
    function submitBookingForm(form) {
        if (!validateBookingForm(form)) {
            return;
        }

        const formData = new FormData(form);
        const url = '/api/v1/beautybooking/bookings';

        fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + window.authToken
            },
            body: formData
        })
        .then(async response => {
            if (!response.ok) {
                // Handle HTTP errors
                // مدیریت خطاهای HTTP
                // Read response body once before passing to error handler
                // خواندن body پاسخ یک بار قبل از ارسال به error handler
                let errorData = {};
                try {
                    errorData = await response.json();
                } catch (e) {
                    errorData = {};
                }
                
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                    // Pass pre-parsed errorData to avoid reading response body twice
                    // ارسال errorData از پیش parse شده برای جلوگیری از خواندن body پاسخ دوباره
                    await window.BeautyErrorHandler.showFetchError(response, null, errorData);
                }
                
                showBookingError(errorData.errors || [{ message: errorData.message || '{{ translate("Booking failed") }}' }]);
                return null;
            }
            return response.json();
        })
        .then(result => {
            if (result && result.data) {
                // Success - redirect or show success message
                // موفقیت - هدایت یا نمایش پیام موفقیت
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.showUserMessage) {
                    window.BeautyErrorHandler.showUserMessage('{{ translate("Booking created successfully") }}', 'success');
                }
                window.location.href = '/bookings/' + result.data.id;
            } else if (result && result.errors) {
                // Error - show error message
                // خطا - نمایش پیام خطا
                showBookingError(result.errors);
            }
        })
        .catch(async error => {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                await window.BeautyErrorHandler.showFetchError(null, error);
            } else {
                console.error('Error submitting booking:', error);
            }
            showBookingError([{ message: error.message || '{{ translate("Booking failed") }}' }]);
        });
    }

    /**
     * Show booking error
     * نمایش خطای رزرو
     *
     * @param {Array} errors
     */
    function showBookingError(errors) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML = '<ul>' + errors.map(e => '<li>' + e.message + '</li>').join('') + '</ul>';
        document.querySelector('.booking-form').prepend(errorDiv);
    }

    /**
     * Translate helper function (fallback if translate is not available)
     * تابع کمکی ترجمه (پشتیبان در صورت نبودن translate)
     *
     * @param {string} key Translation key
     * @returns {string|null} Translated string or null if not found
     */
    function translate(key) {
        if (typeof window.translate === 'function') {
            return window.translate(key);
        }
        // Try to use Laravel's translate if available
        // تلاش برای استفاده از translate لاراول در صورت موجود بودن
        if (typeof window.Laravel !== 'undefined' && window.Laravel.translations) {
            // Return null if translation not found (not the key itself)
            // برگرداندن null در صورت عدم یافتن ترجمه (نه خود کلید)
            return window.Laravel.translations[key] || null;
        }
        return null;
    }

    // Initialize booking form handlers
    // راه‌اندازی کنترل‌کننده‌های فرم رزرو
    document.addEventListener('DOMContentLoaded', function() {
        const bookingForm = document.querySelector('.booking-form');
        if (bookingForm) {
            // Clear errors on field change
            // پاک کردن خطاها هنگام تغییر فیلد
            const formFields = bookingForm.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                field.addEventListener('change', function() {
                    clearFieldError(this);
                });
                field.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });

            // Handle date change
            // مدیریت تغییر تاریخ
            const dateInput = bookingForm.querySelector('[name="booking_date"]');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    clearFieldError(this);
                    const salonId = bookingForm.querySelector('[name="salon_id"]')?.value;
                    const serviceId = bookingForm.querySelector('[name="service_id"]')?.value;
                    const staffId = bookingForm.querySelector('[name="staff_id"]')?.value;

                    if (salonId && serviceId && this.value) {
                        loadAvailableSlots(salonId, serviceId, this.value, staffId || null);
                    }
                });
            }

            // Handle time change
            // مدیریت تغییر زمان
            const timeInput = bookingForm.querySelector('[name="booking_time"]');
            if (timeInput) {
                timeInput.addEventListener('change', function() {
                    clearFieldError(this);
                });
            }

            // Handle form submission
            // مدیریت ارسال فرم
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBookingForm(this);
            });
        }
    });

    // Export functions for global use
    // صادر کردن توابع برای استفاده سراسری
    window.BeautyBooking = {
        checkAvailability: checkAvailability,
        loadAvailableSlots: loadAvailableSlots,
        validateForm: validateBookingForm,
        submitForm: submitBookingForm,
        showFieldError: showFieldError,
        clearFieldError: clearFieldError,
        clearAllFieldErrors: clearAllFieldErrors
    };
})();

