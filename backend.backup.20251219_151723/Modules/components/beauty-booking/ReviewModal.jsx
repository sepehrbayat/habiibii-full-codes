import React, { useState } from 'react';
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Button,
    TextField,
    Rating,
    Box,
    Typography,
    Alert,
    IconButton,
} from '@mui/material';
import { Close } from '@mui/icons-material';
import axios from 'axios';

/**
 * Review Modal Component
 * کامپوننت مودال ثبت نظر
 */
const ReviewModal = ({ open, onClose, bookingId, salonId, serviceId, onSuccess, apiBaseUrl, csrfToken }) => {
    const [rating, setRating] = useState(0);
    const [comment, setComment] = useState('');
    const [attachments, setAttachments] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSubmit = async () => {
        if (rating === 0) {
            setError(window.translate?.('Please provide a rating') || 'Please provide a rating');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            formData.append('rating', rating);
            formData.append('comment', comment);
            
            attachments.forEach((file) => {
                formData.append('attachments[]', file);
            });

            const response = await axios.post(
                `${apiBaseUrl}/customer/reviews`,
                formData,
                {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json',
                    },
                }
            );

            if (response.data && response.data.message) {
                onSuccess && onSuccess(response.data.data);
                handleClose();
            }
        } catch (err) {
            setError(
                err.response?.data?.errors?.[0]?.message ||
                err.response?.data?.message ||
                'Failed to submit review'
            );
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        setRating(0);
        setComment('');
        setAttachments([]);
        setError(null);
        onClose();
    };

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);
        setAttachments(files);
    };

    return (
        <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
            <DialogTitle>
                <Box display="flex" justifyContent="space-between" alignItems="center">
                    <Typography variant="h6">
                        {window.translate?.('Write a Review') || 'Write a Review'}
                    </Typography>
                    <IconButton onClick={handleClose} size="small">
                        <Close />
                    </IconButton>
                </Box>
            </DialogTitle>
            <DialogContent>
                {error && (
                    <Alert severity="error" sx={{ mb: 2 }}>
                        {error}
                    </Alert>
                )}

                <Box mb={3}>
                    <Typography variant="subtitle2" gutterBottom>
                        {window.translate?.('Rating') || 'Rating'} *
                    </Typography>
                    <Rating
                        value={rating}
                        onChange={(event, newValue) => setRating(newValue)}
                        size="large"
                    />
                </Box>

                <TextField
                    fullWidth
                    multiline
                    rows={4}
                    label={window.translate?.('Comment') || 'Comment'}
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    placeholder={window.translate?.('Share your experience...') || 'Share your experience...'}
                    sx={{ mb: 2 }}
                />

                <Box>
                    <Typography variant="subtitle2" gutterBottom>
                        {window.translate?.('Attachments (Optional)') || 'Attachments (Optional)'}
                    </Typography>
                    <input
                        type="file"
                        accept="image/*"
                        multiple
                        onChange={handleFileChange}
                        style={{ marginTop: 8 }}
                    />
                    {attachments.length > 0 && (
                        <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 1 }}>
                            {attachments.length} {window.translate?.('file(s) selected') || 'file(s) selected'}
                        </Typography>
                    )}
                </Box>
            </DialogContent>
            <DialogActions>
                <Button onClick={handleClose} disabled={loading}>
                    {window.translate?.('Cancel') || 'Cancel'}
                </Button>
                <Button
                    onClick={handleSubmit}
                    variant="contained"
                    disabled={loading || rating === 0}
                >
                    {loading
                        ? (window.translate?.('Submitting...') || 'Submitting...')
                        : (window.translate?.('Submit Review') || 'Submit Review')}
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default ReviewModal;

