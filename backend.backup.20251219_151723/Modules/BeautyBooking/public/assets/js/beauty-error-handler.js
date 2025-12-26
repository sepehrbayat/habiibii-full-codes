/**
 * Beauty Booking Error Handler Utility
 * ابزار مدیریت خطا برای رزرو زیبایی
 *
 * Centralized error handling functions for consistent error display
 * توابع مدیریت خطای متمرکز برای نمایش یکپارچه خطاها
 */

(function() {
    'use strict';

    /**
     * Default retry configuration
     * تنظیمات پیش‌فرض تلاش مجدد
     */
    const DEFAULT_RETRY_CONFIG = {
        maxRetries: 2,
        retryDelay: 1000, // milliseconds
        retryableStatusCodes: [408, 429, 500, 502, 503, 504] // Network and server errors
    };

    /**
     * Show user-friendly error message
     * نمایش پیام خطای کاربرپسند
     *
     * @param {string} message Error message
     * @param {string} type Message type (error, warning, info, success)
     */
    function showUserMessage(message, type = 'error') {
        // Try to use Toastr if available
        // تلاش برای استفاده از Toastr در صورت موجود بودن
        if (typeof toastr !== 'undefined') {
            switch(type) {
                case 'error':
                    toastr.error(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                case 'info':
                    toastr.info(message);
                    break;
                case 'success':
                    toastr.success(message);
                    break;
                default:
                    toastr.error(message);
            }
        } else {
            // Fallback to Bootstrap alert
            // استفاده از هشدار Bootstrap در صورت نبودن Toastr
            const alertClass = type === 'error' ? 'alert-danger' : 
                              type === 'warning' ? 'alert-warning' : 
                              type === 'info' ? 'alert-info' : 'alert-success';
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            // Insert at top of page or in a specific container
            // درج در بالای صفحه یا در یک کانتینر خاص
            const container = document.querySelector('.main-content') || 
                             document.querySelector('main') || 
                             document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-remove after 5 seconds
            // حذف خودکار پس از 5 ثانیه
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    }

    /**
     * Parse API error response
     * تجزیه پاسخ خطای API
     *
     * @param {Response|Object} response Fetch response or error object
     * @returns {string} User-friendly error message
     */
    function parseApiError(response) {
        // If response has errors array (Laravel format)
        // اگر پاسخ دارای آرایه errors باشد (فرمت Laravel)
        if (response.errors && Array.isArray(response.errors)) {
            return response.errors.map(err => err.message || err).join(', ');
        }
        
        // If response has error message
        // اگر پاسخ دارای پیام خطا باشد
        if (response.message) {
            return response.message;
        }
        
        // If response has error property
        // اگر پاسخ دارای ویژگی error باشد
        if (response.error) {
            return response.error;
        }
        
        // Default message
        // پیام پیش‌فرض
        return translate('messages.something_went_wrong') || 'Something went wrong. Please try again.';
    }

    /**
     * Handle jQuery AJAX errors
     * مدیریت خطاهای jQuery AJAX
     *
     * @param {jqXHR} xhr jQuery XHR object
     * @param {string} status Status text
     * @param {string} error Error message
     */
    function showAjaxError(xhr, status, error) {
        let errorMessage = '';
        
        // Handle different error types
        // مدیریت انواع مختلف خطا
        if (status === 'timeout') {
            errorMessage = translate('messages.request_timeout') || 'Request timeout. Please try again.';
        } else if (status === 'abort') {
            errorMessage = translate('messages.request_cancelled') || 'Request was cancelled.';
        } else if (status === 'parsererror') {
            errorMessage = translate('messages.invalid_response') || 'Invalid response from server.';
        } else if (xhr.status === 0) {
            errorMessage = translate('messages.network_error') || 'Network error. Please check your connection.';
        } else if (xhr.status === 401) {
            errorMessage = translate('messages.unauthorized') || 'You are not authorized. Please login again.';
            // Optionally redirect to login
            // اختیاری: هدایت به صفحه ورود
            if (window.location.pathname.indexOf('/login') === -1) {
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            }
        } else if (xhr.status === 403) {
            errorMessage = translate('messages.forbidden') || 'You do not have permission to perform this action.';
        } else if (xhr.status === 404) {
            errorMessage = translate('messages.not_found') || 'The requested resource was not found.';
        } else if (xhr.status === 422) {
            // Validation errors
            // خطاهای اعتبارسنجی
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.errors) {
                    errorMessage = parseApiError(response);
                } else {
                    errorMessage = translate('messages.validation_failed') || 'Validation failed. Please check your input.';
                }
            } catch (e) {
                errorMessage = translate('messages.validation_failed') || 'Validation failed. Please check your input.';
            }
        } else if (xhr.status >= 500) {
            errorMessage = translate('messages.server_error') || 'Server error. Please try again later.';
        } else {
            // Try to parse error response
            // تلاش برای تجزیه پاسخ خطا
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = parseApiError(response);
            } catch (e) {
                errorMessage = error || translate('messages.something_went_wrong') || 'Something went wrong. Please try again.';
            }
        }
        
        showUserMessage(errorMessage, 'error');
    }

    /**
     * Handle Fetch API errors
     * مدیریت خطاهای Fetch API
     *
     * @param {Response} response Fetch response object (optional if errorData provided)
     * @param {Error} error Error object
     * @param {Object} errorData Pre-parsed error data (optional, to avoid reading response body twice)
     * @returns {Promise<Object>} Object with errorMessage and errorData properties
     */
    async function showFetchError(response, error, errorData = null) {
        let errorMessage = '';
        
        // Network error (no response)
        // خطای شبکه (بدون پاسخ)
        if (!response) {
            errorMessage = translate('messages.network_error') || 'Network error. Please check your connection.';
            showUserMessage(errorMessage, 'error');
            return { errorMessage, errorData: null };
        }
        
        // Parse response body if not already provided
        // تجزیه body پاسخ در صورت عدم ارائه قبلی
        if (errorData === null) {
            try {
                // Clone response before reading to allow caller to read it again if needed
                // کلون کردن پاسخ قبل از خواندن برای اجازه خواندن دوباره توسط فراخواننده در صورت نیاز
                const responseClone = response.clone();
                errorData = await responseClone.json();
            } catch (e) {
                // Response body is not JSON or already consumed
                // body پاسخ JSON نیست یا قبلاً مصرف شده است
                errorData = null;
            }
        }
        
        // Handle different HTTP status codes
        // مدیریت کدهای وضعیت HTTP مختلف
        if (response.status === 401) {
            errorMessage = translate('messages.unauthorized') || 'You are not authorized. Please login again.';
            if (window.location.pathname.indexOf('/login') === -1) {
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            }
        } else if (response.status === 403) {
            errorMessage = translate('messages.forbidden') || 'You do not have permission to perform this action.';
        } else if (response.status === 404) {
            errorMessage = translate('messages.not_found') || 'The requested resource was not found.';
        } else if (response.status === 422) {
            // Validation errors
            // خطاهای اعتبارسنجی
            if (errorData) {
                errorMessage = parseApiError(errorData);
            } else {
                errorMessage = translate('messages.validation_failed') || 'Validation failed. Please check your input.';
            }
        } else if (response.status >= 500) {
            errorMessage = translate('messages.server_error') || 'Server error. Please try again later.';
        } else {
            // Try to parse error response
            // تلاش برای تجزیه پاسخ خطا
            if (errorData) {
                errorMessage = parseApiError(errorData);
            } else {
                errorMessage = translate('messages.something_went_wrong') || 'Something went wrong. Please try again.';
            }
        }
        
        showUserMessage(errorMessage, 'error');
        return { errorMessage, errorData };
    }

    /**
     * Handle API error response
     * مدیریت پاسخ خطای API
     *
     * @param {Object} response API response object
     */
    function handleApiError(response) {
        const errorMessage = parseApiError(response);
        showUserMessage(errorMessage, 'error');
    }

    /**
     * Retry a failed request with exponential backoff
     * تلاش مجدد برای درخواست ناموفق با تاخیر نمایی
     *
     * @param {Function} requestFn Function that returns a Promise
     * @param {Object} config Retry configuration
     * @returns {Promise} Request promise
     */
    async function retryRequest(requestFn, config = {}) {
        const retryConfig = { ...DEFAULT_RETRY_CONFIG, ...config };
        let lastError = null;
        
        for (let attempt = 0; attempt <= retryConfig.maxRetries; attempt++) {
            try {
                const response = await requestFn();
                
                // Check if response indicates retryable error
                // بررسی اینکه آیا پاسخ نشان‌دهنده خطای قابل تلاش مجدد است
                if (response && response.status && retryConfig.retryableStatusCodes.includes(response.status)) {
                    if (attempt < retryConfig.maxRetries) {
                        const delay = retryConfig.retryDelay * Math.pow(2, attempt);
                        showUserMessage(
                            translate('messages.retrying_request') || `Retrying... (${attempt + 1}/${retryConfig.maxRetries})`,
                            'info'
                        );
                        await new Promise(resolve => setTimeout(resolve, delay));
                        continue;
                    }
                }
                
                return response;
            } catch (error) {
                lastError = error;
                
                // Only retry on network errors or retryable status codes
                // فقط در خطاهای شبکه یا کدهای وضعیت قابل تلاش مجدد، تلاش مجدد
                if (attempt < retryConfig.maxRetries) {
                    const delay = retryConfig.retryDelay * Math.pow(2, attempt);
                    showUserMessage(
                        translate('messages.retrying_request') || `Retrying... (${attempt + 1}/${retryConfig.maxRetries})`,
                        'info'
                    );
                    await new Promise(resolve => setTimeout(resolve, delay));
                } else {
                    throw lastError;
                }
            }
        }
        
        throw lastError;
    }

    /**
     * Translate helper function (fallback if translate is not available)
     * تابع کمکی ترجمه (پشتیبان در صورت نبودن translate)
     *
     * @param {string} key Translation key
     * @returns {string} Translated string or key
     */
    function translate(key) {
        if (typeof window.translate === 'function') {
            return window.translate(key);
        }
        return null;
    }

    // Export functions for global use
    // صادر کردن توابع برای استفاده سراسری
    window.BeautyErrorHandler = {
        showAjaxError: showAjaxError,
        showFetchError: showFetchError,
        showUserMessage: showUserMessage,
        handleApiError: handleApiError,
        retryRequest: retryRequest,
        parseApiError: parseApiError
    };
})();

