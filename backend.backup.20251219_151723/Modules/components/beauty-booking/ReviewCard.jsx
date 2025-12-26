import React from 'react';
import {
    Card,
    CardContent,
    Box,
    Typography,
    Avatar,
    Rating,
    Chip,
} from '@mui/material';
import { Person } from '@mui/icons-material';

/**
 * Review Card Component
 * کامپوننت کارت نظر
 */
const ReviewCard = ({ review }) => {
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <Card sx={{ mb: 2 }}>
            <CardContent>
                <Box display="flex" alignItems="start" gap={2}>
                    <Avatar>
                        {review.user?.f_name?.[0] || review.user?.name?.[0] || <Person />}
                    </Avatar>
                    <Box flexGrow={1}>
                        <Box display="flex" justifyContent="space-between" alignItems="start" mb={1}>
                            <Box>
                                <Typography variant="subtitle1" fontWeight={600}>
                                    {review.user?.f_name && review.user?.l_name
                                        ? `${review.user.f_name} ${review.user.l_name}`
                                        : review.user?.name || 'Anonymous'}
                                </Typography>
                                <Typography variant="caption" color="text.secondary">
                                    {formatDate(review.created_at)}
                                </Typography>
                            </Box>
                            <Rating value={review.rating} readOnly size="small" />
                        </Box>
                        {review.comment && (
                            <Typography variant="body2" color="text.secondary" paragraph>
                                {review.comment}
                            </Typography>
                        )}
                        {review.attachments && review.attachments.length > 0 && (
                            <Box display="flex" gap={1} flexWrap="wrap" mt={1}>
                                {review.attachments.map((attachment, index) => (
                                    <img
                                        key={index}
                                        src={attachment.startsWith('http') ? attachment : `${window.APP_URL || ''}/storage/app/public/${attachment}`}
                                        alt={`Review ${index + 1}`}
                                        style={{
                                            width: 80,
                                            height: 80,
                                            objectFit: 'cover',
                                            borderRadius: 4,
                                        }}
                                    />
                                ))}
                            </Box>
                        )}
                        {review.service && (
                            <Chip
                                label={review.service.name}
                                size="small"
                                sx={{ mt: 1 }}
                            />
                        )}
                    </Box>
                </Box>
            </CardContent>
        </Card>
    );
};

export default ReviewCard;

