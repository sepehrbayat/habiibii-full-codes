import React, { useEffect, useMemo, useState } from "react";
import {
  Typography,
  Box,
  Chip,
  Button,
  Divider,
  Card,
  CardContent,
  Modal,
  Rating,
  TextField,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetBookingDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetBookingDetails";
import useGetBookingConversation from "../../../../../api-manage/hooks/react-query/beauty/useGetBookingConversation";
import { useCancelBooking } from "../../../../../api-manage/hooks/react-query/beauty/useCancelBooking";
import { useRescheduleBooking } from "../../../../../api-manage/hooks/react-query/beauty/useRescheduleBooking";
import { useRouter } from "next/router";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { CircularProgress } from "@mui/material";
import ReviewForm from "./ReviewForm";
import ReviewCard from "./ReviewCard";
import BookingConversation from "./BookingConversation";
import dayjs from "dayjs";
import { DatePicker, TimePicker } from "@mui/x-date-pickers";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";

const BookingDetails = ({ bookingId }) => {
  const router = useRouter();
  const { data, isLoading, refetch } = useGetBookingDetails(bookingId, !!bookingId);
  const {
    data: conversationData,
    isLoading: conversationLoading,
    refetch: refetchConversation,
  } = useGetBookingConversation(bookingId, { enabled: !!bookingId });
  const { mutate: cancelBooking, isLoading: isCancelling } = useCancelBooking();
  const { mutate: rescheduleBooking, isLoading: isRescheduling } = useRescheduleBooking();
  const [reviewModalOpen, setReviewModalOpen] = useState(false);
  const [rescheduleModalOpen, setRescheduleModalOpen] = useState(false);
  const [rescheduleDate, setRescheduleDate] = useState(null);
  const [rescheduleTime, setRescheduleTime] = useState(null);
  const [rescheduleNotes, setRescheduleNotes] = useState("");
  const [cancelModalOpen, setCancelModalOpen] = useState(false);
  const [cancellationReason, setCancellationReason] = useState("");

  const booking = data?.data || data;
  const conversation = conversationData?.data;

  // Initialize reschedule date/time with current booking values when modal opens
  useEffect(() => {
    if (rescheduleModalOpen && booking?.booking_date && booking?.booking_time) {
      const currentDate = dayjs(booking.booking_date);
      const [hours, minutes] = booking.booking_time.split(":").map(Number);
      const currentTime = dayjs().hour(hours).minute(minutes).second(0);
      setRescheduleDate(currentDate);
      setRescheduleTime(currentTime);
    }
  }, [rescheduleModalOpen, booking?.booking_date, booking?.booking_time]);

  const isWithin24Hours = useMemo(() => {
    if (!booking?.booking_date || !booking?.booking_time) return false;
    const bookingDateTime = dayjs(
      `${booking.booking_date} ${booking.booking_time}`,
      "YYYY-MM-DD HH:mm"
    );
    return bookingDateTime.diff(dayjs(), "hour") < 24;
  }, [booking?.booking_date, booking?.booking_time]);

  useEffect(() => {
    const interval = setInterval(() => {
      if (bookingId) {
        refetchConversation();
      }
    }, 10000);
    return () => clearInterval(interval);
  }, [bookingId, refetchConversation]);

  const handleCancel = () => {
    if (!cancellationReason.trim()) {
      toast.error("Please provide a cancellation reason");
      return;
    }

    cancelBooking(
      { id: bookingId, cancellationReason },
      {
        onSuccess: () => {
          toast.success("Booking cancelled successfully");
          setCancelModalOpen(false);
          setCancellationReason("");
          refetch();
        },
        onError: (error) => {
          const code = error?.response?.data?.errors?.[0]?.code;
          if (code === "cancellation_window_passed") {
            toast.error("Cancellation is not allowed within 24 hours of the appointment.");
          } else {
            toast.error(getBeautyErrorMessage(error) || "Failed to cancel booking");
          }
        },
      }
    );
  };

  const handleReschedule = () => {
    if (!rescheduleDate || !rescheduleTime) {
      toast.error("Please select new date and time");
      return;
    }

    if (isWithin24Hours) {
      toast.error("Reschedule must be at least 24 hours before the appointment");
      return;
    }

    const formattedDate = rescheduleDate.format("YYYY-MM-DD");
    const formattedTime = rescheduleTime.format("HH:mm");

    rescheduleBooking(
      {
        id: bookingId,
        booking_date: formattedDate,
        booking_time: formattedTime,
        notes: rescheduleNotes || undefined,
      },
      {
        onSuccess: () => {
          toast.success("Booking rescheduled successfully");
          setRescheduleModalOpen(false);
          setRescheduleNotes("");
          refetch();
        },
        onError: (error) => {
          // Handle 429 Too Many Requests
          // مدیریت 429 Too Many Requests
          if (error?.response?.status === 429 || error?.response?.statusCode === 429) {
            const retryAfter = error?.response?.headers?.['retry-after'] || error?.response?.headers?.['x-ratelimit-reset'];
            const message = retryAfter 
              ? `Too many requests. Please wait ${retryAfter} seconds before trying again.`
              : "Too many requests. Please wait a moment before trying again.";
            toast.error(message);
            return;
          }

          const errs = error?.response?.data?.errors || [];
          const code = errs[0]?.code;
          const availableSlots = errs[0]?.available_slots;
          if (code === "slot_unavailable") {
            toast.error(
              availableSlots?.length
                ? `Selected slot unavailable. Available: ${availableSlots.join(", ")}`
                : "Selected slot is unavailable. Please pick another time."
            );
          } else if (code === "cancellation_window_passed") {
            toast.error("Reschedule not allowed within 24 hours of appointment.");
          } else {
            toast.error(getBeautyErrorMessage(error) || "Failed to reschedule booking");
          }
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

  // Format current booking date and time for display
  const currentBookingDate = booking?.booking_date 
    ? dayjs(booking.booking_date).format("MMMM DD, YYYY")
    : "N/A";
  const currentBookingTime = booking?.booking_time || "N/A";

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
                <Typography variant="h5" fontWeight="bold">
                  {booking.salon_name || "Salon"}
                </Typography>
                <Typography variant="body1" color="text.secondary">
                  {booking.service_name || "Service"}
                </Typography>
              </Box>
              <Chip
                label={booking.status || "pending"}
                color={getStatusColor(booking.status)}
              />
            </Box>

            <Divider />

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Booking Reference
              </Typography>
              <Typography variant="body1" fontWeight="bold">
                {booking.booking_reference || `#${booking.id}`}
              </Typography>
            </Box>

            {booking.cancellation_deadline && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Cancellation Deadline
                </Typography>
                <Typography variant="body1" fontWeight="bold">
                  {booking.cancellation_deadline}
                </Typography>
              </Box>
            )}

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Booking Date & Time
              </Typography>
              <Typography variant="body1" fontWeight="bold">
                {booking.booking_date} at {booking.booking_time}
              </Typography>
            </Box>

            {booking.cancellation_fee ? (
              <Typography variant="body2" color="error">
                Cancellation fee may apply: ${booking.cancellation_fee}
              </Typography>
            ) : null}

            {isWithin24Hours && (
              <Typography variant="body2" color="error">
                Cancellation/Reschedule must be at least 24 hours before appointment.
              </Typography>
            )}

            {booking.service && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Service Details
                </Typography>
                <Typography variant="body1">
                  {booking.service.name} - {booking.service.duration_minutes} minutes
                </Typography>
                <Typography variant="body1" color="primary" fontWeight="bold">
                  ${booking.service.price}
                </Typography>
              </Box>
            )}

            {booking.staff && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Staff Member
                </Typography>
                <Typography variant="body1">{booking.staff.name}</Typography>
              </Box>
            )}

            {booking.salon && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Salon
                </Typography>
                <Typography variant="body1" fontWeight="bold">
                  {booking.salon.name}
                </Typography>
                {booking.salon.phone && (
                  <Typography variant="body2" color="text.secondary">
                    Phone: {booking.salon.phone}
                  </Typography>
                )}
                {booking.salon.email && (
                  <Typography variant="body2" color="text.secondary">
                    Email: {booking.salon.email}
                  </Typography>
                )}
              </Box>
            )}

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Total Amount
              </Typography>
              <Typography variant="h6" color="primary" fontWeight="bold">
                ${booking.total_amount || 0}
              </Typography>
            </Box>

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Payment Status
              </Typography>
              <Chip
                label={booking.payment_status || "unpaid"}
                color={booking.payment_status === "paid" ? "success" : "warning"}
                size="small"
              />
            </Box>

            {booking.notes && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Notes
                </Typography>
                <Typography variant="body1">{booking.notes}</Typography>
              </Box>
            )}

      {booking.can_cancel && booking.status !== "cancelled" && booking.status !== "completed" && (
              <Button
                variant="outlined"
                color="error"
          onClick={() => setCancelModalOpen(true)}
          disabled={isCancelling || isWithin24Hours}
                fullWidth
              >
                {isCancelling ? "Cancelling..." : "Cancel Booking"}
              </Button>
            )}

      {booking.can_reschedule && (
              <Button
                variant="contained"
                color="secondary"
                onClick={() => setRescheduleModalOpen(true)}
          disabled={isRescheduling || isWithin24Hours}
                fullWidth
              >
                {isRescheduling ? "Rescheduling..." : "Reschedule Booking"}
              </Button>
            )}

            {booking.status === "completed" && !booking.review && (
              <Button
                variant="contained"
                color="primary"
                onClick={() => setReviewModalOpen(true)}
                fullWidth
              >
                Submit Review
              </Button>
            )}

            {booking.review && (
              <Box>
                <Divider sx={{ my: 2 }} />
                <Typography variant="h6" fontWeight="bold" gutterBottom>
                  Your Review
                </Typography>
                <ReviewCard review={booking.review} />
              </Box>
            )}
          </CustomStackFullWidth>
        </CardContent>
      </Card>

      <BookingConversation
        conversation={conversation}
        isLoading={conversationLoading}
        bookingId={bookingId}
        onRefresh={refetchConversation}
      />

      <Modal
        open={reviewModalOpen}
        onClose={() => setReviewModalOpen(false)}
        aria-labelledby="review-modal-title"
      >
        <Box
          sx={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            width: { xs: "90%", md: 600 },
            bgcolor: "background.paper",
            boxShadow: 24,
            p: 4,
            borderRadius: 2,
            maxHeight: "90vh",
            overflow: "auto",
          }}
        >
          <ReviewForm
            bookingId={bookingId}
            onSuccess={(response) => {
              setReviewModalOpen(false);
              refetch();
            }}
            onCancel={() => setReviewModalOpen(false)}
          />
        </Box>
      </Modal>

      <LocalizationProvider dateAdapter={AdapterDayjs}>
        <Modal
          open={rescheduleModalOpen}
          onClose={() => setRescheduleModalOpen(false)}
          aria-labelledby="reschedule-modal-title"
        >
          <Box
            sx={{
              position: "absolute",
              top: "50%",
              left: "50%",
              transform: "translate(-50%, -50%)",
              width: { xs: "90%", md: 520 },
              bgcolor: "background.paper",
              boxShadow: 24,
              p: 4,
              borderRadius: 2,
            }}
          >
            <CustomStackFullWidth spacing={2}>
              <Typography id="reschedule-modal-title" variant="h6" fontWeight="bold">
                Reschedule Booking
              </Typography>
              
              {/* Current Schedule Display */}
              <Box
                sx={{
                  p: 2,
                  bgcolor: "grey.100",
                  borderRadius: 1,
                  border: "1px solid",
                  borderColor: "grey.300",
                }}
              >
                <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                  Current Schedule
                </Typography>
                <Typography variant="body1" fontWeight="bold">
                  {currentBookingDate} at {currentBookingTime}
                </Typography>
              </Box>

              <Divider />

              <DatePicker
                label="New Date"
                value={rescheduleDate}
                onChange={(newValue) => setRescheduleDate(newValue)}
                minDate={dayjs().add(1, "day")}
                sx={{
                  width: "100%",
                  "& .MuiInputBase-root": {
                    height: "45px",
                  },
                }}
              />

              <TimePicker
                label="New Time"
                value={rescheduleTime}
                onChange={(newValue) => setRescheduleTime(newValue)}
                sx={{
                  width: "100%",
                  "& .MuiInputBase-root": {
                    height: "45px",
                  },
                }}
              />

              <TextField
                label="Notes (Optional)"
                fullWidth
                multiline
                rows={3}
                value={rescheduleNotes}
                onChange={(e) => setRescheduleNotes(e.target.value)}
              />
              <Box display="flex" gap={2}>
                <Button
                  variant="outlined"
                  color="secondary"
                  fullWidth
                  onClick={() => setRescheduleModalOpen(false)}
                  disabled={isRescheduling}
                >
                  Cancel
                </Button>
                <Button
                  variant="contained"
                  color="primary"
                  fullWidth
                  onClick={handleReschedule}
                  disabled={isRescheduling || !rescheduleDate || !rescheduleTime}
                >
                  {isRescheduling ? "Rescheduling..." : "Confirm"}
                </Button>
              </Box>
            </CustomStackFullWidth>
          </Box>
        </Modal>
      </LocalizationProvider>

      <Modal
        open={cancelModalOpen}
        onClose={() => setCancelModalOpen(false)}
        aria-labelledby="cancel-modal-title"
      >
        <Box
          sx={{
            position: "absolute",
            top: "50%",
            left: "50%",
            transform: "translate(-50%, -50%)",
            width: { xs: "90%", md: 520 },
            bgcolor: "background.paper",
            boxShadow: 24,
            p: 4,
            borderRadius: 2,
          }}
        >
          <CustomStackFullWidth spacing={2}>
            <Typography id="cancel-modal-title" variant="h6" fontWeight="bold">
              Cancel Booking
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Please share a reason for cancellation. If a cancellation fee applies, it will be shown in your total.
            </Typography>
            <TextField
              label="Cancellation Reason"
              fullWidth
              multiline
              rows={3}
              value={cancellationReason}
              onChange={(e) => setCancellationReason(e.target.value)}
            />
            <Box display="flex" gap={2}>
              <Button
                variant="outlined"
                color="secondary"
                fullWidth
                onClick={() => setCancelModalOpen(false)}
                disabled={isCancelling}
              >
                Back
              </Button>
              <Button
                variant="contained"
                color="error"
                fullWidth
                onClick={handleCancel}
                disabled={isCancelling}
              >
                {isCancelling ? "Cancelling..." : "Confirm Cancel"}
              </Button>
            </Box>
          </CustomStackFullWidth>
        </Box>
      </Modal>
    </CustomStackFullWidth>
  );
};

export default BookingDetails;

