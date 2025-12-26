import React, { useState } from "react";
import {
  Typography,
  Box,
  Card,
  CardContent,
  Chip,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  CircularProgress,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetVendorBookingDetails } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorBookingDetails";
import { useConfirmBooking } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useConfirmBooking";
import { useCompleteBooking } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCompleteBooking";
import { useMarkBookingPaid } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useMarkBookingPaid";
import { useCancelVendorBooking } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCancelVendorBooking";
import { toast } from "react-hot-toast";
import { useRouter } from "next/router";
import BookingActions from "./BookingActions";

const VendorBookingDetails = ({ bookingId }) => {
  const router = useRouter();
  const { data, isLoading, refetch } = useGetVendorBookingDetails(bookingId);
  const { mutate: confirmBooking, isLoading: isConfirming } = useConfirmBooking();
  const { mutate: completeBooking, isLoading: isCompleting } = useCompleteBooking();
  const { mutate: markPaid, isLoading: isMarkingPaid } = useMarkBookingPaid();
  const { mutate: cancelBooking, isLoading: isCancelling } = useCancelVendorBooking();
  const [cancelDialogOpen, setCancelDialogOpen] = useState(false);
  const [cancellationReason, setCancellationReason] = useState("");

  const booking = data?.data || data;

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

  const handleConfirm = () => {
    confirmBooking(bookingId, {
      onSuccess: (res) => {
        toast.success(res?.message || "Booking confirmed successfully");
        refetch();
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to confirm booking");
      },
    });
  };

  const handleComplete = () => {
    completeBooking(bookingId, {
      onSuccess: (res) => {
        toast.success(res?.message || "Booking marked as completed");
        refetch();
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to complete booking");
      },
    });
  };

  const handleMarkPaid = () => {
    markPaid(bookingId, {
      onSuccess: (res) => {
        toast.success(res?.message || "Payment marked as paid");
        refetch();
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to mark payment");
      },
    });
  };

  const handleCancel = () => {
    if (!cancellationReason.trim()) {
      toast.error("Please provide a cancellation reason");
      return;
    }
    cancelBooking(
      { bookingId: bookingId, cancellationReason: cancellationReason },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Booking cancelled successfully");
          setCancelDialogOpen(false);
          setCancellationReason("");
          refetch();
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to cancel booking");
        },
      }
    );
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (!booking) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Booking not found</Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Booking Details
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={2}>
            <Box display="flex" justifyContent="space-between" alignItems="flex-start">
              <Box>
                <Typography variant="h6" fontWeight="bold">
                  {booking.service_name || "Service"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Booking Reference: {booking.booking_reference || `#${booking.id}`}
                </Typography>
              </Box>
              <Chip
                label={booking.status || "pending"}
                color={getStatusColor(booking.status)}
                size="small"
              />
            </Box>

            <Box>
              <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
                Customer Information
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Name: {booking.user?.name || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Email: {booking.user?.email || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Phone: {booking.user?.phone || "N/A"}
              </Typography>
            </Box>

            <Box>
              <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
                Booking Information
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Date: {booking.booking_date}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Time: {booking.booking_time}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Total Amount: ${booking.total_amount || 0}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Payment Status: {booking.payment_status || "unpaid"}
              </Typography>
            </Box>

            {booking.staff && (
              <Box>
                <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
                  Staff Information
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Name: {booking.staff.name || "N/A"}
                </Typography>
                {booking.staff.email && (
                  <Typography variant="body2" color="text.secondary">
                    Email: {booking.staff.email}
                  </Typography>
                )}
              </Box>
            )}

            {booking.service && (
              <Box>
                <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
                  Service Information
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Name: {booking.service.name || "N/A"}
                </Typography>
                {booking.service.description && (
                  <Typography variant="body2" color="text.secondary">
                    Description: {booking.service.description}
                  </Typography>
                )}
                <Typography variant="body2" color="text.secondary">
                  Duration: {booking.service.duration_minutes || 0} minutes
                </Typography>
              </Box>
            )}

            <BookingActions
              booking={booking}
              onConfirm={handleConfirm}
              onComplete={handleComplete}
              onMarkPaid={handleMarkPaid}
              onCancel={() => setCancelDialogOpen(true)}
              isConfirming={isConfirming}
              isCompleting={isCompleting}
              isMarkingPaid={isMarkingPaid}
              isCancelling={isCancelling}
            />
          </CustomStackFullWidth>
        </CardContent>
      </Card>

      <Dialog open={cancelDialogOpen} onClose={() => setCancelDialogOpen(false)}>
        <DialogTitle>Cancel Booking</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Cancellation Reason"
            fullWidth
            variant="outlined"
            multiline
            rows={4}
            value={cancellationReason}
            onChange={(e) => setCancellationReason(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setCancelDialogOpen(false)}>Cancel</Button>
          <Button onClick={handleCancel} color="error" disabled={isCancelling}>
            {isCancelling ? "Cancelling..." : "Confirm Cancellation"}
          </Button>
        </DialogActions>
      </Dialog>
    </CustomStackFullWidth>
  );
};

export default VendorBookingDetails;

