import React from 'react';
import {
    Card,
    CardContent,
    CardMedia,
    Typography,
    Box,
    Chip,
    Button,
    Rating,
} from '@mui/material';
import { LocationOn, Star } from '@mui/icons-material';

/**
 * Salon Card Component
 * کامپوننت کارت سالن
 */
const SalonCard = ({ salon, apiBaseUrl }) => {
    const getImageUrl = () => {
        if (salon.store?.image) {
            return salon.store.image.startsWith('http') 
                ? salon.store.image 
                : `${window.APP_URL || ''}/storage/app/public/${salon.store.image}`;
        }
        // Try direct image field
        // تلاش برای فیلد image مستقیم
        if (salon.image) {
            return salon.image.startsWith('http')
                ? salon.image
                : `${window.APP_URL || ''}/storage/app/public/${salon.image}`;
        }
        return `${window.APP_URL || ''}/assets/admin/img/placeholder.png`;
    };

    const getSalonUrl = () => {
        return `${window.APP_URL || ''}/beauty-booking/salon/${salon.id}`;
    };

    const getBusinessTypeLabel = () => {
        return salon.business_type === 'clinic' 
            ? (window.translate?.('Clinic') || 'Clinic')
            : (window.translate?.('Salon') || 'Salon');
    };

    return (
        <Card className="beauty-booking-card" sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
            {salon.store?.image && (
                <CardMedia
                    component="img"
                    height="200"
                    image={getImageUrl()}
                    alt={salon.store?.name || 'Salon'}
                    sx={{ objectFit: 'cover' }}
                />
            )}
            <CardContent sx={{ flexGrow: 1, display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" justifyContent="space-between" alignItems="start" mb={1}>
                    <Typography variant="h6" component="h3" sx={{ fontWeight: 600 }}>
                        {salon.name || salon.store?.name || 'Salon'}
                    </Typography>
                    {salon.badges && salon.badges.length > 0 && (
                        <Box>
                            {salon.badges.map((badge, index) => (
                                <Chip
                                    key={index}
                                    label={badge.replace('_', ' ')}
                                    size="small"
                                    color="success"
                                    sx={{ ml: 0.5 }}
                                />
                            ))}
                        </Box>
                    )}
                </Box>

                {(salon.store?.address || salon.address) && (
                    <Box display="flex" alignItems="center" mb={1} color="text.secondary">
                        <LocationOn fontSize="small" sx={{ mr: 0.5 }} />
                        <Typography variant="body2">
                            {salon.address || salon.store?.address || 'Address not available'}
                        </Typography>
                    </Box>
                )}

                <Box display="flex" alignItems="center" mb={2}>
                    <Rating
                        value={salon.avg_rating || 0}
                        precision={0.1}
                        readOnly
                        size="small"
                        sx={{ mr: 1 }}
                    />
                    <Typography variant="body2" color="text.secondary">
                        {salon.avg_rating ? salon.avg_rating.toFixed(1) : '0.0'} 
                        ({salon.total_reviews || 0} {window.translate?.('reviews') || 'reviews'})
                    </Typography>
                </Box>

                <Box display="flex" justifyContent="space-between" alignItems="center" mt="auto">
                    <Typography variant="body2" color="text.secondary">
                        {salon.total_bookings || 0} {window.translate?.('bookings') || 'bookings'}
                    </Typography>
                    <Button
                        variant="contained"
                        size="small"
                        href={getSalonUrl()}
                        sx={{ textTransform: 'none' }}
                    >
                        {window.translate?.('View Details') || 'View Details'}
                    </Button>
                </Box>

                {salon.business_type && (
                    <Chip
                        label={getBusinessTypeLabel()}
                        size="small"
                        color={salon.business_type === 'clinic' ? 'info' : 'primary'}
                        sx={{ mt: 1, width: 'fit-content' }}
                    />
                )}
            </CardContent>
        </Card>
    );
};

export default SalonCard;

