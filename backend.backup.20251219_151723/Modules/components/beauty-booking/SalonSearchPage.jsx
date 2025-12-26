import React, { useState, useEffect } from 'react';
import { Box, Container, Typography, Grid, CircularProgress, Alert } from '@mui/material';
import SalonCard from './SalonCard';
import SalonFilter from './SalonFilter';
import axios from 'axios';

/**
 * Salon Search Page Component
 * کامپوننت صفحه جستجوی سالن
 */
const SalonSearchPage = ({ apiBaseUrl = '/api/v1/beautybooking', csrfToken }) => {
    const [salons, setSalons] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState({
        search: '',
        category_id: '',
        business_type: '',
        min_rating: '',
        sort: 'rating',
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
    });

    // Get user location if available
    // دریافت موقعیت کاربر در صورت امکان
    const [userLocation, setUserLocation] = useState(null);

    useEffect(() => {
        // Try to get user location
        // تلاش برای دریافت موقعیت کاربر
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setUserLocation({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    });
                },
                () => {
                    // Location access denied or failed
                    // دسترسی به موقعیت رد شد یا ناموفق بود
                }
            );
        }
    }, []);

    // Fetch salons
    // دریافت سالن‌ها
    const fetchSalons = async (page = 1) => {
        setLoading(true);
        setError(null);

        try {
            const params = {
                page,
                per_page: pagination.per_page,
                ...filters,
            };

            // Add location if available
            // اضافه کردن موقعیت در صورت امکان
            if (userLocation) {
                params.latitude = userLocation.latitude;
                params.longitude = userLocation.longitude;
            }

            const response = await axios.get(`${apiBaseUrl}/customer/salons/search`, {
                params,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (response.data) {
                // Handle both paginated and simple list responses
                // مدیریت پاسخ‌های صفحه‌بندی شده و لیست ساده
                const salonsData = Array.isArray(response.data.data) 
                    ? response.data.data 
                    : (Array.isArray(response.data) ? response.data : []);
                
                setSalons(salonsData);
                
                if (response.data.meta) {
                    setPagination({
                        current_page: response.data.meta.current_page || 1,
                        last_page: response.data.meta.last_page || 1,
                        per_page: response.data.meta.per_page || 12,
                        total: response.data.meta.total || response.data.total || salonsData.length,
                    });
                } else if (response.data.total !== undefined) {
                    setPagination(prev => ({
                        ...prev,
                        total: response.data.total,
                    }));
                }
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load salons');
            console.error('Error fetching salons:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchSalons(1);
    }, [filters, userLocation]);

    const handleFilterChange = (newFilters) => {
        setFilters((prev) => ({ ...prev, ...newFilters }));
        setPagination((prev) => ({ ...prev, current_page: 1 }));
    };

    const handlePageChange = (event, page) => {
        fetchSalons(page);
    };

    return (
        <Container maxWidth="lg" className="beauty-booking-search">
            <Box mb={4}>
                <Typography variant="h4" component="h1" gutterBottom>
                    {window.translate?.('Find Your Perfect Salon') || 'Find Your Perfect Salon'}
                </Typography>
                <Typography variant="body1" color="text.secondary">
                    {window.translate?.('Search and book appointments at top-rated salons') || 
                     'Search and book appointments at top-rated salons'}
                </Typography>
            </Box>

            <SalonFilter
                filters={filters}
                onFilterChange={handleFilterChange}
                apiBaseUrl={apiBaseUrl}
                csrfToken={csrfToken}
            />

            {error && (
                <Alert severity="error" sx={{ mb: 3 }}>
                    {error}
                </Alert>
            )}

            {loading ? (
                <Box display="flex" justifyContent="center" alignItems="center" minHeight="400px">
                    <CircularProgress />
                </Box>
            ) : salons.length === 0 ? (
                <Alert severity="info" sx={{ mt: 3 }}>
                    {window.translate?.('No salons found. Try adjusting your search criteria.') || 
                     'No salons found. Try adjusting your search criteria.'}
                </Alert>
            ) : (
                <>
                    <Grid container spacing={3} sx={{ mt: 2 }}>
                        {salons.map((salon) => (
                            <Grid item xs={12} sm={6} md={4} key={salon.id}>
                                <SalonCard salon={salon} apiBaseUrl={apiBaseUrl} />
                            </Grid>
                        ))}
                    </Grid>

                    {/* Pagination would go here */}
                    {/* صفحه‌بندی اینجا قرار می‌گیرد */}
                </>
            )}
        </Container>
    );
};

export default SalonSearchPage;

