import React from "react";
import { Card, CardContent, Typography, Box, Chip, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";

const BookingCard = ({ booking, onViewDetails }) => {
  const router = useRouter();

  const getStatusColor = (status) => {
    switch (status) {
      case "confirmed":
        return "success";
      case "pending":
        return "warning";
      case "cancelled":
        return "error";
      case "completed":
        return "info";
      default:
        return "default";
    }
  };

  const handleViewDetails = () => {
    if (onViewDetails) {
      onViewDetails(booking.id);
    } else {
      router.push(`/beauty/bookings/${booking.id}`);
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {booking.salon_name || "Salon"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {booking.service_name || "Service"}
              </Typography>
            </Box>
            <Chip
              label={booking.status || "pending"}
              color={getStatusColor(booking.status)}
              size="small"
            />
          </Box>

          <Box>
            <Typography variant="body2" color="text.secondary">
              Booking Reference: {booking.booking_reference || `#${booking.id}`}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Date: {booking.booking_date} at {booking.booking_time}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Amount: ${booking.total_amount || 0}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Payment Status: {booking.payment_status || "unpaid"}
            </Typography>
          </Box>

          <Button
            variant="outlined"
            color="primary"
            onClick={handleViewDetails}
            fullWidth
          >
            View Details
          </Button>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default BookingCard;

