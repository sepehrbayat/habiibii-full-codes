/**
 * Beauty Booking Confirmation Dialog Utility
 * ابزار دیالوگ تأیید رزرو زیبایی
 *
 * Handles confirmation dialogs for destructive actions
 * مدیریت دیالوگ‌های تأیید برای اقدامات مخرب
 */

(function() {
    'use strict';

    /**
     * Show confirmation dialog
     * نمایش دیالوگ تأیید
     *
     * @param {string} message Confirmation message
     * @param {string} title Dialog title (optional)
     * @param {string} confirmText Confirm button text (optional)
     * @param {string} cancelText Cancel button text (optional)
     * @returns {Promise<boolean>} Promise that resolves to true if confirmed, false if cancelled
     */
    function showConfirmation(message, title = null, confirmText = null, cancelText = null) {
        return new Promise((resolve) => {
            const displayTitle = title || translate('messages.confirm_action') || 'Confirm Action';
            const displayConfirmText = confirmText || translate('messages.confirm') || 'Confirm';
            const displayCancelText = cancelText || translate('messages.cancel') || 'Cancel';

            // Use browser confirm for simple cases
            // استفاده از confirm مرورگر برای موارد ساده
            if (confirm(message)) {
                resolve(true);
            } else {
                resolve(false);
            }
        });
    }

    /**
     * Show Bootstrap modal confirmation dialog
     * نمایش دیالوگ تأیید با Bootstrap modal
     *
     * @param {string} message Confirmation message
     * @param {string} title Dialog title (optional)
     * @param {string} confirmText Confirm button text (optional)
     * @param {string} cancelText Cancel button text (optional)
     * @param {string} confirmClass Button class (danger, warning, etc.)
     * @returns {Promise<boolean>} Promise that resolves to true if confirmed, false if cancelled
     */
    function showModalConfirmation(message, title = null, confirmText = null, cancelText = null, confirmClass = 'btn-danger') {
        return new Promise((resolve) => {
            // Check if Bootstrap modal is available
            // بررسی موجود بودن Bootstrap modal
            if (typeof $ === 'undefined' || typeof $.fn.modal === 'undefined') {
                // Fallback to browser confirm
                // استفاده از confirm مرورگر
                return showConfirmation(message, title, confirmText, cancelText).then(resolve);
            }

            const displayTitle = title || translate('messages.confirm_action') || 'Confirm Action';
            const displayConfirmText = confirmText || translate('messages.confirm') || 'Confirm';
            const displayCancelText = cancelText || translate('messages.cancel') || 'Cancel';

            // Create modal HTML
            // ایجاد HTML modal
            const modalId = 'beauty-confirmation-modal-' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${displayTitle}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">${displayCancelText}</button>
                                <button type="button" class="btn ${confirmClass} beauty-confirm-btn">${displayConfirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Append modal to body
            // افزودن modal به body
            $('body').append(modalHtml);

            const $modal = $('#' + modalId);

            // Handle confirm button click
            // مدیریت کلیک دکمه تأیید
            $modal.find('.beauty-confirm-btn').on('click', function() {
                $modal.modal('hide');
                resolve(true);
            });

            // Handle modal close
            // مدیریت بستن modal
            $modal.on('hidden.bs.modal', function() {
                $(this).remove();
                resolve(false);
            });

            // Show modal
            // نمایش modal
            $modal.modal('show');
        });
    }

    /**
     * Initialize confirmation dialogs for elements with data-confirm attribute
     * راه‌اندازی دیالوگ‌های تأیید برای عناصر با ویژگی data-confirm
     */
    function initializeConfirmDialogs() {
        // Handle links and buttons with data-confirm attribute
        // مدیریت لینک‌ها و دکمه‌ها با ویژگی data-confirm
        document.addEventListener('click', function(e) {
            let target = e.target.closest('a[data-confirm], button[data-confirm], form[data-confirm], [data-confirm]');
            
            // Also handle form-alert pattern (existing pattern in codebase)
            // همچنین مدیریت الگوی form-alert (الگوی موجود در کدبیس)
            if (!target) {
                target = e.target.closest('a.form-alert, button.form-alert, .form-alert');
            }

            if (!target) {
                return;
            }

            // Get confirmation message from data-confirm or data-message
            // دریافت پیام تأیید از data-confirm یا data-message
            let confirmMessage = target.getAttribute('data-confirm') || target.getAttribute('data-message');
            
            // For form-alert, check if it has data-message
            // برای form-alert، بررسی وجود data-message
            if (!confirmMessage && target.classList.contains('form-alert')) {
                confirmMessage = target.getAttribute('data-message');
            }

            if (!confirmMessage) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const confirmTitle = target.getAttribute('data-confirm-title');
            const confirmText = target.getAttribute('data-confirm-text');
            const cancelText = target.getAttribute('data-cancel-text');
            const confirmClass = target.getAttribute('data-confirm-class') || 'btn-danger';
            const useModal = target.hasAttribute('data-confirm-modal');

            const confirmationFn = useModal ? showModalConfirmation : showConfirmation;

            confirmationFn(confirmMessage, confirmTitle, confirmText, cancelText, confirmClass)
                .then(confirmed => {
                    if (confirmed) {
                        // For form-alert pattern, submit the associated form
                        // برای الگوی form-alert، ارسال فرم مرتبط
                        if (target.classList.contains('form-alert') && target.hasAttribute('data-id')) {
                            const formId = target.getAttribute('data-id');
                            const form = document.getElementById(formId);
                            if (form) {
                                form.submit();
                                return;
                            }
                        }

                        // Execute the original action
                        // اجرای اقدام اصلی
                        if (target.tagName === 'A' && target.href && target.href !== 'javascript:') {
                            window.location.href = target.href;
                        } else if (target.tagName === 'FORM') {
                            target.submit();
                        } else if (target.tagName === 'BUTTON' && target.type === 'submit') {
                            const form = target.closest('form');
                            if (form) {
                                form.submit();
                            }
                        } else if (target.onclick) {
                            // Execute onclick handler if it's a function
                            // اجرای handler onclick در صورت تابع بودن
                            if (typeof target.onclick === 'function') {
                                target.onclick();
                            }
                        } else if (target.getAttribute('onclick')) {
                            // SECURITY: Do not execute onclick attributes with eval()
                            // امنیت: عدم اجرای ویژگی‌های onclick با eval()
                            // Legacy onclick handlers are not supported for security reasons
                            // handlerهای onclick قدیمی به دلایل امنیتی پشتیبانی نمی‌شوند
                            // Use data attributes or event listeners instead
                            // به جای آن از data attributes یا event listener استفاده کنید
                            console.warn('Legacy onclick attribute detected but not executed for security reasons. Please use data attributes or event listeners instead.');
                            console.warn('Attribute value:', target.getAttribute('onclick'));
                        }
                    }
                });
        });

        // Handle form submissions with data-confirm
        // مدیریت ارسال فرم‌ها با data-confirm
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.hasAttribute('data-confirm')) {
                const confirmMessage = form.getAttribute('data-confirm');
                if (confirmMessage) {
                    e.preventDefault();
                    e.stopPropagation();

                    showConfirmation(confirmMessage)
                        .then(confirmed => {
                            if (confirmed) {
                                form.submit();
                            }
                        });
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

    // Initialize on DOM ready
    // راه‌اندازی هنگام آماده شدن DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeConfirmDialogs);
    } else {
        initializeConfirmDialogs();
    }

    // Export functions for global use
    // صادر کردن توابع برای استفاده سراسری
    window.BeautyConfirmation = {
        showConfirmation: showConfirmation,
        showModalConfirmation: showModalConfirmation,
        initializeConfirmDialogs: initializeConfirmDialogs
    };
})();

