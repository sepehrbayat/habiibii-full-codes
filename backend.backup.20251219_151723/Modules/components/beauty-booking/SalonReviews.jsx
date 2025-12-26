import React, { useState, useEffect } from 'react';
import { Box, Typography, CircularProgress, Alert, Button } from '@mui/material';
import ReviewCard from './ReviewCard';
import ReviewModal from './ReviewModal';
import RatingDisplay from './RatingDisplay';
import axios from 'axios';

/**
 * Salon Reviews Component
 * کامپوننت نظرات سالن
 */
const SalonReviews = ({ salonId, apiBaseUrl, csrfToken }) => {
    const [reviews, setReviews] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [reviewModalOpen, setReviewModalOpen] = useState(false);
    const [salonData, setSalonData] = useState(null);

    useEffect(() => {
        fetchReviews();
        fetchSalonData();
    }, [salonId]);

    const fetchReviews = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get(`${apiBaseUrl}/customer/reviews/${salonId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                params: {
                    limit: 25,
                    offset: 0,
                },
            });

            if (response.data) {
                // Handle paginated response
                // مدیریت پاسخ صفحه‌بندی شده
                const reviewsData = Array.isArray(response.data.data) 
                    ? response.data.data 
                    : [];
                setReviews(reviewsData);
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load reviews');
            console.error('Error fetching reviews:', err);
        } finally {
            setLoading(false);
        }
    };

    const fetchSalonData = async () => {
        try {
            const response = await axios.get(`${apiBaseUrl}/customer/salons/${salonId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (response.data && response.data.data) {
                setSalonData(response.data.data);
            }
        } catch (err) {
            console.error('Error fetching salon data:', err);
        }
    };

    const handleReviewSubmit = (newReview) => {
        // Refresh reviews after submission
        // به‌روزرسانی نظرات بعد از ارسال
        fetchReviews();
        fetchSalonData();
    };

    // Check if user has a completed booking for this salon
    // بررسی اینکه آیا کاربر رزرو تکمیل شده برای این سالن دارد
    const [canReview, setCanReview] = useState(false);
    const [bookingId, setBookingId] = useState(null);

    useEffect(() => {
        const checkCanReview = async () => {
            try {
                // Check if user is authenticated by trying to get bookings
                // بررسی احراز هویت کاربر با تلاش برای دریافت رزروها
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    // Try to get user bookings
                    const response = await axios.get(`${apiBaseUrl}/customer/bookings`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        params: {
                            salon_id: salonId,
                            status: 'completed',
                        },
                    });

                    if (response.data && response.data.data && response.data.data.length > 0) {
                        // Check if user hasn't reviewed this booking yet
                        const completedBooking = response.data.data.find(booking => 
                            booking.salon_id === salonId && 
                            booking.status === 'completed'
                        );
                        
                        if (completedBooking) {
                            // Check if review already exists
                            const hasReview = reviews.some(review => review.booking_id === completedBooking.id);
                            if (!hasReview) {
                                setCanReview(true);
                                setBookingId(completedBooking.id);
                            }
                        }
                    }
                }
            } catch (err) {
                // User might not be authenticated or no bookings
                // کاربر ممکن است احراز هویت نشده باشد یا رزروی نداشته باشد
                if (err.response?.status !== 401) {
                    console.log('Cannot review:', err);
                }
            }
        };

        if (reviews.length > 0 || !loading) {
            checkCanReview();
        }
    }, [salonId, reviews, loading]);

    return (
        <Box>
            {salonData && (
                <Box mb={3}>
                    <RatingDisplay
                        rating={salonData.avg_rating || 0}
                        reviewCount={salonData.total_reviews || 0}
                        size="large"
                    />
                </Box>
            )}

            {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                    {error}
                </Alert>
            )}

            {loading ? (
                <Box display="flex" justifyContent="center" alignItems="center" minHeight="200px">
                    <CircularProgress />
                </Box>
            ) : reviews.length === 0 ? (
                <Typography variant="body2" color="text.secondary">
                    {window.translate?.('No reviews yet') || 'No reviews yet'}
                </Typography>
            ) : (
                <Box>
                    {reviews.map((review) => (
                        <ReviewCard key={review.id} review={review} />
                    ))}
                </Box>
            )}

            {canReview && bookingId && (
                <>
                    <Button
                        variant="contained"
                        onClick={() => setReviewModalOpen(true)}
                        sx={{ mt: 2 }}
                    >
                        {window.translate?.('Write a Review') || 'Write a Review'}
                    </Button>
                    <ReviewModal
                        open={reviewModalOpen}
                        onClose={() => setReviewModalOpen(false)}
                        bookingId={bookingId}
                        salonId={salonId}
                        serviceId={null}
                        onSuccess={handleReviewSubmit}
                        apiBaseUrl={apiBaseUrl}
                        csrfToken={csrfToken}
                    />
                </>
            )}
        </Box>
    );
};

export default SalonReviews;

