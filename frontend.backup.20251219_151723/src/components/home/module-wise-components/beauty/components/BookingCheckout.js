import React, { useMemo, useState } from "react";
import { Typography, Box, Button, Card, CardContent, FormControl, InputLabel, Select, MenuItem } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetBookingDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetBookingDetails";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import { useRouter } from "next/router";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { CircularProgress } from "@mui/material";
import { useSelector } from "react-redux";

const BookingCheckout = ({ bookingId }) => {
  const router = useRouter();
  const { configData } = useSelector((state) => state.configData);
  const { data, isLoading } = useGetBookingDetails(bookingId, !!bookingId);
  const booking = data?.data || data;
  const [paymentMethod, setPaymentMethod] = useState("cash_payment");
  const [paymentGateway, setPaymentGateway] = useState("");

  const digitalGateways = useMemo(
    () =>
      configData?.active_payment_method_list?.filter(
        (method) => method?.type === "digital_payment"
      ) || [],
    [configData?.active_payment_method_list]
  );

  const handlePayment = async () => {
    const normalizedMethod =
      paymentMethod === "online" ? "digital_payment" : paymentMethod;

    if (normalizedMethod === "digital_payment" && !paymentGateway) {
      toast.error("Please select a payment gateway");
      return;
    }

    try {
      const response = await BeautyApi.processPayment({
        booking_id: bookingId,
        payment_method: normalizedMethod,
        payment_gateway:
          normalizedMethod === "digital_payment" ? paymentGateway : undefined,
        callback_url:
          typeof window !== "undefined"
            ? `${window.location.origin}/beauty/bookings/${bookingId}`
            : undefined,
      });

      if (response.data?.data?.redirect_url) {
        window.location.href = response.data.data.redirect_url;
      } else {
        toast.success("Payment processed successfully");
        router.push(`/beauty/bookings/${bookingId}`);
      }
    } catch (error) {
      toast.error(getBeautyErrorMessage(error) || "Payment failed");
    }
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
        Complete Payment
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={3}>
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {booking.salon_name || "Salon"}
              </Typography>
              <Typography variant="body1" color="text.secondary">
                {booking.service_name || "Service"}
              </Typography>
            </Box>

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Booking Date & Time
              </Typography>
              <Typography variant="body1">
                {booking.booking_date} at {booking.booking_time}
              </Typography>
            </Box>

            <Box>
              <Typography variant="h5" color="primary" fontWeight="bold">
                Total Amount: ${booking.total_amount || 0}
              </Typography>
            </Box>

            <FormControl fullWidth>
              <InputLabel>Payment Method</InputLabel>
              <Select
                value={paymentMethod}
                label="Payment Method"
                onChange={(e) => {
                  setPaymentMethod(e.target.value);
                  if (e.target.value !== "digital_payment") {
                    setPaymentGateway("");
                  }
                }}
              >
                <MenuItem value="cash_payment">Cash on Delivery</MenuItem>
                <MenuItem value="wallet">Wallet</MenuItem>
                <MenuItem value="digital_payment">Digital Payment</MenuItem>
              </Select>
            </FormControl>

            {paymentMethod === "digital_payment" && (
              <FormControl fullWidth required>
                <InputLabel>Payment Gateway</InputLabel>
                <Select
                  value={paymentGateway}
                  label="Payment Gateway"
                  onChange={(e) => setPaymentGateway(e.target.value)}
                >
                  <MenuItem value="">Select Gateway</MenuItem>
                  {digitalGateways.map((gateway) => (
                    <MenuItem key={gateway.gateway} value={gateway.gateway}>
                      {gateway.gateway_title || gateway.gateway}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            )}

            <Button
              variant="contained"
              color="primary"
              size="large"
              onClick={handlePayment}
              fullWidth
            >
              Pay Now
            </Button>
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default BookingCheckout;

