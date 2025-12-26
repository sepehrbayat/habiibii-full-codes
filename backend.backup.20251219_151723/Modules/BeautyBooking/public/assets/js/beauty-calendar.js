/**
 * Beauty Calendar JavaScript
 * جاوااسکریپت تقویم زیبایی
 *
 * FullCalendar integration for beauty booking calendar
 * یکپارچه‌سازی FullCalendar برای تقویم رزرو زیبایی
 */

(function() {
    'use strict';

    /**
     * Initialize FullCalendar
     * راه‌اندازی FullCalendar
     */
    function initBeautyCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            return;
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: document.documentElement.lang || 'en',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: calendarEl.dataset.eventsUrl || window.beautyCalendarEventsUrl || '/vendor-panel/beautybooking/calendar/get-bookings',
                method: 'GET',
                failure: function(info) {
                    if (window.BeautyErrorHandler && window.BeautyErrorHandler.showUserMessage) {
                        window.BeautyErrorHandler.showUserMessage(
                            '{{ translate("messages.failed_to_load_calendar_events") }}' || 'Failed to load calendar events',
                            'error'
                        );
                    } else {
                        console.error('Failed to load calendar events:', info);
                    }
                }
            },
            eventClick: function(info) {
                // Handle event click (show booking details)
                // مدیریت کلیک روی رویداد (نمایش جزئیات رزرو)
                if (info.event.extendedProps.booking_reference) {
                    const bookingUrl = calendarEl.dataset.bookingUrl || '/vendor-panel/beautybooking/booking/show/';
                    window.location.href = bookingUrl + info.event.id;
                }
            },
            eventMouseEnter: function(info) {
                // Show tooltip on hover
                // نمایش راهنما هنگام هاور
                if (info.event.extendedProps) {
                    const tooltip = `
                        <div class="calendar-tooltip">
                            <strong>${info.event.title}</strong><br>
                            ${info.event.extendedProps.booking_reference || ''}<br>
                            Status: ${info.event.extendedProps.status || ''}
                        </div>
                    `;
                    $(info.el).tooltip({
                        title: tooltip,
                        html: true,
                        placement: 'top'
                    });
                }
            },
            eventColor: '#007bff',
            eventTextColor: '#ffffff',
        });

        calendar.render();

        // Store calendar instance for later use
        // ذخیره نمونه تقویم برای استفاده بعدی
        window.beautyCalendar = calendar;
    }

    /**
     * Create calendar block
     * ایجاد بلاک تقویم
     */
    function createCalendarBlock(date, startTime, endTime, type, reason) {
        const url = '/vendor-panel/beautybooking/calendar/blocks/store';
        const data = {
            date: date,
            start_time: startTime,
            end_time: endTime,
            type: type,
            reason: reason,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            if (!response.ok) {
                // Handle HTTP errors
                // مدیریت خطاهای HTTP
                // Read response body once before passing to error handler
                // خواندن body پاسخ یک بار قبل از ارسال به error handler
                let errorData = null;
                try {
                    errorData = await response.json();
                } catch (e) {
                    errorData = null;
                }
                
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                    // Pass pre-parsed errorData to avoid reading response body twice
                    // ارسال errorData از پیش parse شده برای جلوگیری از خواندن body پاسخ دوباره
                    await window.BeautyErrorHandler.showFetchError(response, null, errorData);
                }
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Refresh calendar
                // تازه‌سازی تقویم
                if (window.beautyCalendar) {
                    window.beautyCalendar.refetchEvents();
                }
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.showUserMessage) {
                    window.BeautyErrorHandler.showUserMessage(
                        '{{ translate("messages.calendar_block_created_successfully") }}' || 'Calendar block created successfully',
                        'success'
                    );
                }
            } else if (data && data.errors) {
                // Show validation errors
                // نمایش خطاهای اعتبارسنجی
                if (window.BeautyErrorHandler && window.BeautyErrorHandler.handleApiError) {
                    window.BeautyErrorHandler.handleApiError(data);
                }
            }
        })
        .catch(async error => {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showFetchError) {
                await window.BeautyErrorHandler.showFetchError(null, error);
            } else {
                console.error('Error creating calendar block:', error);
            }
        });
    }

    // Initialize on document ready
    // راه‌اندازی پس از آماده شدن سند
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBeautyCalendar);
    } else {
        initBeautyCalendar();
    }

    // Export functions for global use
    // صادر کردن توابع برای استفاده سراسری
    window.BeautyCalendar = {
        init: initBeautyCalendar,
        createBlock: createCalendarBlock,
        getInstance: function() {
            return window.beautyCalendar;
        }
    };
})();

