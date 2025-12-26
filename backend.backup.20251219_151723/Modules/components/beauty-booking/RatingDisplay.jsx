import React from 'react';
import { Rating, Box, Typography } from '@mui/material';

/**
 * Rating Display Component
 * کامپوننت نمایش امتیاز
 */
const RatingDisplay = ({ 
    rating = 0, 
    reviewCount = 0, 
    size = 'medium',
    showText = true,
    showCount = true 
}) => {
    return (
        <Box display="flex" alignItems="center" gap={1}>
            <Rating
                value={rating}
                precision={0.1}
                readOnly
                size={size}
            />
            {showText && (
                <Typography variant="body2" color="text.secondary">
                    {rating.toFixed(1)}
                </Typography>
            )}
            {showCount && reviewCount > 0 && (
                <Typography variant="body2" color="text.secondary">
                    ({reviewCount} {window.translate?.('reviews') || 'reviews'})
                </Typography>
            )}
        </Box>
    );
};

export default RatingDisplay;

