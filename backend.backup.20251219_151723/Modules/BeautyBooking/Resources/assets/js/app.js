/**
 * Beauty Booking React App Entry Point
 * نقطه ورود اپلیکیشن React رزرو زیبایی
 */

import React from 'react';
import { createRoot } from 'react-dom/client';

// Import components
// وارد کردن کامپوننت‌ها
import SalonSearchPage from '../../../components/beauty-booking/SalonSearchPage';
import SalonReviews from '../../../components/beauty-booking/SalonReviews';

// Initialize React app when DOM is ready
// راه‌اندازی اپلیکیشن React وقتی DOM آماده است
document.addEventListener('DOMContentLoaded', function() {
    // Mount SalonSearchPage if container exists
    // نصب SalonSearchPage اگر container وجود داشته باشد
    const searchContainer = document.getElementById('beauty-salon-search-root');
    if (searchContainer) {
        const root = createRoot(searchContainer);
        root.render(React.createElement(SalonSearchPage, {
            apiBaseUrl: (window.APP_URL || '') + '/api/v1/beautybooking',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        }));
    }

    // Mount SalonReviews if container exists
    // نصب SalonReviews اگر container وجود داشته باشد
    const reviewsContainer = document.getElementById('beauty-salon-reviews-root');
    if (reviewsContainer) {
        const salonId = reviewsContainer.getAttribute('data-salon-id');
        const root = createRoot(reviewsContainer);
        root.render(React.createElement(SalonReviews, {
            salonId: parseInt(salonId),
            apiBaseUrl: (window.APP_URL || '') + '/api/v1/beautybooking',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        }));
    }

    // Handle review modal button in booking detail page
    // مدیریت دکمه مودال نظر در صفحه جزئیات رزرو
    const bookingReviewBtn = document.getElementById('open-booking-review-modal-btn');
    if (bookingReviewBtn && window.bookingData) {
        bookingReviewBtn.addEventListener('click', function() {
            // Import ReviewModal dynamically
            // وارد کردن ReviewModal به صورت پویا
            import('../../../components/beauty-booking/ReviewModal').then(({ default: ReviewModal }) => {
                const modalContainer = document.createElement('div');
                modalContainer.id = 'booking-review-modal-container';
                document.body.appendChild(modalContainer);
                
                const root = createRoot(modalContainer);
                const bookingId = parseInt(this.getAttribute('data-booking-id'));
                const salonId = parseInt(this.getAttribute('data-salon-id'));
                const serviceId = this.getAttribute('data-service-id') ? parseInt(this.getAttribute('data-service-id')) : null;
                
                root.render(React.createElement(ReviewModal, {
                    open: true,
                    onClose: () => {
                        root.unmount();
                        document.body.removeChild(modalContainer);
                    },
                    bookingId: bookingId,
                    salonId: salonId,
                    serviceId: serviceId,
                    onSuccess: () => {
                        // Reload page to show new review
                        // بارگذاری مجدد صفحه برای نمایش نظر جدید
                        window.location.reload();
                    },
                    apiBaseUrl: (window.APP_URL || '') + '/api/v1/beautybooking',
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                }));
            });
        });
    }
});

