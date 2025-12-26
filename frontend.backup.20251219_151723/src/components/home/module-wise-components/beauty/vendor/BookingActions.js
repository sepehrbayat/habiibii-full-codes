import React from "react";
import { Box, Button, Stack } from "@mui/material";

const BookingActions = ({
  booking,
  onConfirm,
  onComplete,
  onMarkPaid,
  onCancel,
  isConfirming,
  isCompleting,
  isMarkingPaid,
  isCancelling,
}) => {
  const status = booking?.status || "pending";
  const paymentStatus = booking?.payment_status || "unpaid";

  return (
    <Box sx={{ mt: 2 }}>
      <Stack direction="row" spacing={2} flexWrap="wrap">
        {status === "pending" && (
          <Button
            variant="contained"
            color="success"
            onClick={onConfirm}
            disabled={isConfirming}
          >
            {isConfirming ? "Confirming..." : "Confirm Booking"}
          </Button>
        )}

        {status === "confirmed" && (
          <Button
            variant="contained"
            color="primary"
            onClick={onComplete}
            disabled={isCompleting}
          >
            {isCompleting ? "Completing..." : "Mark as Completed"}
          </Button>
        )}

        {paymentStatus === "unpaid" && booking?.payment_method === "cash_payment" && (
          <Button
            variant="contained"
            color="info"
            onClick={onMarkPaid}
            disabled={isMarkingPaid}
          >
            {isMarkingPaid ? "Marking..." : "Mark Payment as Paid"}
          </Button>
        )}

        {status !== "cancelled" && status !== "completed" && (
          <Button
            variant="outlined"
            color="error"
            onClick={onCancel}
            disabled={isCancelling}
          >
            Cancel Booking
          </Button>
        )}
      </Stack>
    </Box>
  );
};

export default BookingActions;

