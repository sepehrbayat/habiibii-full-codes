/**
 * Beauty Booking Validation Utility
 * ابزار اعتبارسنجی رزرو زیبایی
 *
 * Reusable validation functions for forms
 * توابع اعتبارسنجی قابل استفاده مجدد برای فرم‌ها
 */

(function() {
    'use strict';

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
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        fieldContainer.appendChild(errorDiv);
    }

    /**
     * Clear inline error message for a field
     * پاک کردن پیام خطای درون خطی برای یک فیلد
     *
     * @param {HTMLElement} field Input field element
     */
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        const existingError = fieldContainer.querySelector('.invalid-feedback[data-field-error="' + (field.name || field.id) + '"]');
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
     * Validate salon registration form
     * اعتبارسنجی فرم ثبت‌نام سالن
     *
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateSalonRegistrationForm(form) {
        clearAllFieldErrors(form);
        let isValid = true;

        // Validate business name
        // اعتبارسنجی نام کسب‌وکار
        const businessNameField = form.querySelector('[name="business_name"], [name="name"]');
        if (businessNameField) {
            const businessName = businessNameField.value.trim();
            if (!businessName || businessName.length < 2) {
                showFieldError(businessNameField, translate('messages.business_name_required') || 'Business name is required (minimum 2 characters)');
                isValid = false;
            }
        }

        // Validate license number
        // اعتبارسنجی شماره مجوز
        const licenseField = form.querySelector('[name="license_number"]');
        if (licenseField && licenseField.hasAttribute('required')) {
            const licenseNumber = licenseField.value.trim();
            if (!licenseNumber) {
                showFieldError(licenseField, translate('messages.license_number_required') || 'License number is required');
                isValid = false;
            }
        }

        // Validate contact phone
        // اعتبارسنجی تلفن تماس
        const phoneField = form.querySelector('[name="phone"], [name="contact_phone"]');
        if (phoneField && phoneField.hasAttribute('required')) {
            const phone = phoneField.value.trim();
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phone) {
                showFieldError(phoneField, translate('messages.phone_required') || 'Phone number is required');
                isValid = false;
            } else if (!phoneRegex.test(phone)) {
                showFieldError(phoneField, translate('messages.invalid_phone_format') || 'Invalid phone number format');
                isValid = false;
            }
        }

        // Validate email if provided
        // اعتبارسنجی ایمیل در صورت ارائه
        const emailField = form.querySelector('[name="email"]');
        if (emailField && emailField.value) {
            const email = emailField.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError(emailField, translate('messages.invalid_email_format') || 'Invalid email format');
                isValid = false;
            }
        }

        // Validate location coordinates if provided
        // اعتبارسنجی مختصات مکان در صورت ارائه
        const latitudeField = form.querySelector('[name="latitude"]');
        const longitudeField = form.querySelector('[name="longitude"]');
        if (latitudeField && longitudeField && latitudeField.value && longitudeField.value) {
            const lat = parseFloat(latitudeField.value);
            const lng = parseFloat(longitudeField.value);
            if (isNaN(lat) || lat < -90 || lat > 90) {
                showFieldError(latitudeField, translate('messages.invalid_latitude') || 'Invalid latitude (must be between -90 and 90)');
                isValid = false;
            }
            if (isNaN(lng) || lng < -180 || lng > 180) {
                showFieldError(longitudeField, translate('messages.invalid_longitude') || 'Invalid longitude (must be between -180 and 180)');
                isValid = false;
            }
        }

        // Validate document uploads
        // اعتبارسنجی آپلود اسناد
        const documentFields = form.querySelectorAll('input[type="file"][name*="document"]');
        documentFields.forEach(field => {
            if (field.hasAttribute('required') && field.files.length === 0) {
                showFieldError(field, translate('messages.document_required') || 'Document is required');
                isValid = false;
            } else if (field.files.length > 0) {
                // Validate file type and size
                // اعتبارسنجی نوع و اندازه فایل
                Array.from(field.files).forEach(file => {
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                    
                    if (file.size > maxSize) {
                        showFieldError(field, translate('messages.file_too_large') || 'File size must be less than 5MB');
                        isValid = false;
                    }
                    
                    if (!allowedTypes.includes(file.type)) {
                        showFieldError(field, translate('messages.invalid_file_type') || 'Invalid file type. Allowed: JPEG, PNG, PDF');
                        isValid = false;
                    }
                });
            }
        });

        return isValid;
    }

    /**
     * Validate service creation form
     * اعتبارسنجی فرم ایجاد خدمت
     *
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateServiceForm(form) {
        clearAllFieldErrors(form);
        let isValid = true;

        // Validate service name
        // اعتبارسنجی نام خدمت
        const nameField = form.querySelector('[name="name"]');
        if (nameField) {
            const name = nameField.value.trim();
            if (!name || name.length < 2) {
                showFieldError(nameField, translate('messages.service_name_required') || 'Service name is required (minimum 2 characters)');
                isValid = false;
            }
        }

        // Validate price
        // اعتبارسنجی قیمت
        const priceField = form.querySelector('[name="price"]');
        if (priceField) {
            const price = parseFloat(priceField.value);
            if (isNaN(price) || price <= 0) {
                showFieldError(priceField, translate('messages.price_must_be_positive') || 'Price must be a positive number');
                isValid = false;
            }
        }

        // Validate duration
        // اعتبارسنجی مدت زمان
        const durationField = form.querySelector('[name="duration_minutes"]');
        if (durationField) {
            const duration = parseInt(durationField.value);
            if (isNaN(duration) || duration <= 0) {
                showFieldError(durationField, translate('messages.duration_must_be_positive') || 'Duration must be a positive integer (in minutes)');
                isValid = false;
            }
        }

        // Validate category
        // اعتبارسنجی دسته‌بندی
        const categoryField = form.querySelector('[name="category_id"]');
        if (categoryField && categoryField.hasAttribute('required')) {
            const categoryId = categoryField.value;
            if (!categoryId || categoryId === '' || categoryId === '0') {
                showFieldError(categoryField, translate('messages.category_required') || 'Please select a category');
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Validate staff creation form
     * اعتبارسنجی فرم ایجاد کارمند
     *
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateStaffForm(form) {
        clearAllFieldErrors(form);
        let isValid = true;

        // Validate staff name
        // اعتبارسنجی نام کارمند
        const nameField = form.querySelector('[name="name"]');
        if (nameField) {
            const name = nameField.value.trim();
            if (!name || name.length < 2) {
                showFieldError(nameField, translate('messages.staff_name_required') || 'Staff name is required (minimum 2 characters)');
                isValid = false;
            }
        }

        // Validate phone
        // اعتبارسنجی تلفن
        const phoneField = form.querySelector('[name="phone"]');
        if (phoneField && phoneField.hasAttribute('required')) {
            const phone = phoneField.value.trim();
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phone) {
                showFieldError(phoneField, translate('messages.phone_required') || 'Phone number is required');
                isValid = false;
            } else if (!phoneRegex.test(phone)) {
                showFieldError(phoneField, translate('messages.invalid_phone_format') || 'Invalid phone number format');
                isValid = false;
            }
        }

        // Validate email if provided
        // اعتبارسنجی ایمیل در صورت ارائه
        const emailField = form.querySelector('[name="email"]');
        if (emailField && emailField.value) {
            const email = emailField.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError(emailField, translate('messages.invalid_email_format') || 'Invalid email format');
                isValid = false;
            }
        }

        // Validate working hours format if provided (JSON)
        // اعتبارسنجی فرمت ساعات کاری در صورت ارائه (JSON)
        const workingHoursField = form.querySelector('[name="working_hours"]');
        if (workingHoursField && workingHoursField.value) {
            try {
                const workingHours = JSON.parse(workingHoursField.value);
                if (!Array.isArray(workingHours) && typeof workingHours !== 'object') {
                    showFieldError(workingHoursField, translate('messages.invalid_working_hours_format') || 'Invalid working hours format');
                    isValid = false;
                }
            } catch (e) {
                showFieldError(workingHoursField, translate('messages.invalid_working_hours_format') || 'Invalid working hours format (must be valid JSON)');
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Initialize form validation
     * راه‌اندازی اعتبارسنجی فرم
     *
     * @param {HTMLFormElement} form
     * @param {string} formType Form type: 'salon', 'service', 'staff'
     */
    function initializeFormValidation(form, formType) {
        if (!form) {
            return;
        }

        // Clear errors on field change
        // پاک کردن خطاها هنگام تغییر فیلد
        const formFields = form.querySelectorAll('input, select, textarea');
        formFields.forEach(field => {
            field.addEventListener('change', function() {
                clearFieldError(this);
            });
            field.addEventListener('input', function() {
                clearFieldError(this);
            });
        });

        // Handle form submission
        // مدیریت ارسال فرم
        form.addEventListener('submit', function(e) {
            let isValid = false;

            switch(formType) {
                case 'salon':
                    isValid = validateSalonRegistrationForm(form);
                    break;
                case 'service':
                    isValid = validateServiceForm(form);
                    break;
                case 'staff':
                    isValid = validateStaffForm(form);
                    break;
                default:
                    isValid = true;
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                
                // Scroll to first error
                // اسکرول به اولین خطا
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }

    /**
     * Translate helper function
     * تابع کمکی ترجمه
     *
     * @param {string} key Translation key
     * @returns {string} Translated string or null
     */
    function translate(key) {
        if (typeof window.translate === 'function') {
            return window.translate(key);
        }
        if (typeof window.Laravel !== 'undefined' && window.Laravel.translations) {
            return window.Laravel.translations[key] || null;
        }
        return null;
    }

    // Auto-initialize forms on page load
    // راه‌اندازی خودکار فرم‌ها هنگام بارگذاری صفحه
    document.addEventListener('DOMContentLoaded', function() {
        // Salon registration form
        // فرم ثبت‌نام سالن
        const salonForm = document.querySelector('form[data-form-type="salon"], .salon-registration-form');
        if (salonForm) {
            initializeFormValidation(salonForm, 'salon');
        }

        // Service form
        // فرم خدمت
        const serviceForm = document.querySelector('form[data-form-type="service"], .service-form');
        if (serviceForm) {
            initializeFormValidation(serviceForm, 'service');
        }

        // Staff form
        // فرم کارمند
        const staffForm = document.querySelector('form[data-form-type="staff"], .staff-form');
        if (staffForm) {
            initializeFormValidation(staffForm, 'staff');
        }
    });

    // Export functions for global use
    // صادر کردن توابع برای استفاده سراسری
    window.BeautyValidation = {
        validateSalonRegistrationForm: validateSalonRegistrationForm,
        validateServiceForm: validateServiceForm,
        validateStaffForm: validateStaffForm,
        initializeFormValidation: initializeFormValidation,
        showFieldError: showFieldError,
        clearFieldError: clearFieldError,
        clearAllFieldErrors: clearAllFieldErrors
    };
})();

