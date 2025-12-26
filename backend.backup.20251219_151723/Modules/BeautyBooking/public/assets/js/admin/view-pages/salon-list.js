"use strict";

// Salon List Page JavaScript
// جاوااسکریپت صفحه لیست سالن‌ها

document.addEventListener('DOMContentLoaded', function() {
    // Status change handler
    // هندلر تغییر وضعیت
    $('.status_change_alert').on('change', function() {
        let url = $(this).data('url');
        let message = $(this).data('message');
        
        if (confirm(message)) {
            $.get({
                url: url,
                success: function(data) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('{{ translate("messages.status_updated_successfully") }}');
                    } else if (window.BeautyErrorHandler && window.BeautyErrorHandler.showUserMessage) {
                        window.BeautyErrorHandler.showUserMessage('{{ translate("messages.status_updated_successfully") }}', 'success');
                    }
                },
                error: function(xhr, status, error) {
                    if (window.BeautyErrorHandler && window.BeautyErrorHandler.showAjaxError) {
                        window.BeautyErrorHandler.showAjaxError(xhr, status, error);
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error('{{ translate("messages.failed_to_update_status") }}');
                    } else {
                        console.error('Error updating status:', error);
                    }
                }
            });
        } else {
            $(this).prop('checked', !$(this).prop('checked'));
        }
    });
    
    // Export functionality
    // عملکرد صادرات
    $('#export-excel, #export-csv').on('click', function(e) {
        e.preventDefault();
        // Export functionality will be implemented
        // عملکرد صادرات پیاده‌سازی خواهد شد
        toastr.info('{{ translate("messages.export_feature_coming_soon") }}');
    });
});

