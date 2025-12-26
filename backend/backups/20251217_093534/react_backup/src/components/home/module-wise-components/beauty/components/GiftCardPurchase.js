import React, { useMemo, useState } from "react";
import {
  Box,
  Button,
  Card,
  CardContent,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Typography,
  CircularProgress,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useMutation } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { useRouter } from "next/router";
import { useSelector } from "react-redux";

const GiftCardPurchase = () => {
  const router = useRouter();
  const { configData } = useSelector((state) => state.configData);
  const [formData, setFormData] = useState({
    amount: "",
    salon_id: "",
    payment_method: "cash_payment",
    payment_gateway: "",
  });

  const digitalGateways = useMemo(
    () =>
      configData?.active_payment_method_list?.filter(
        (method) => method?.type === "digital_payment"
      ) || [],
    [configData?.active_payment_method_list]
  );

  const { mutate, isLoading } = useMutation(
    (payload) => BeautyApi.purchaseGiftCard(payload),
    {
      onSuccess: (response) => {
        toast.success("Gift card purchase initiated");
        if (response?.data?.redirect_url) {
          window.location.href = response.data.redirect_url;
        } else {
          router.push("/beauty/gift-cards");
        }
      },
      onError: (error) => {
        toast.error(getBeautyErrorMessage(error) || "Failed to purchase gift card");
      },
    }
  );

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!formData.amount) {
      toast.error("Please enter an amount");
      return;
    }

    const paymentMethod =
      formData.payment_method === "online" ? "digital_payment" : formData.payment_method;

    if (paymentMethod === "digital_payment" && !formData.payment_gateway) {
      toast.error("Please select a payment gateway");
      return;
    }

    mutate({
      ...formData,
      payment_method: paymentMethod,
      payment_gateway: paymentMethod === "digital_payment" ? formData.payment_gateway : undefined,
      callback_url:
        typeof window !== "undefined" ? `${window.location.origin}/beauty/gift-cards` : undefined,
    });
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Purchase Gift Card
      </Typography>

      <Card>
        <CardContent>
          <form onSubmit={handleSubmit}>
            <CustomStackFullWidth spacing={2}>
              <TextField
                label="Amount"
                type="number"
                fullWidth
                value={formData.amount}
                onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                inputProps={{ min: 1, step: 1 }}
                required
              />
              <TextField
                label="Salon ID (optional)"
                fullWidth
                value={formData.salon_id}
                onChange={(e) => setFormData({ ...formData, salon_id: e.target.value })}
              />

              <FormControl fullWidth>
                <InputLabel>Payment Method</InputLabel>
                <Select
                  value={formData.payment_method}
                  label="Payment Method"
                  onChange={(e) =>
                    setFormData({
                      ...formData,
                      payment_method: e.target.value,
                      payment_gateway: e.target.value === "digital_payment" ? formData.payment_gateway : "",
                    })
                  }
                >
                  <MenuItem value="cash_payment">Cash</MenuItem>
                  <MenuItem value="wallet">Wallet</MenuItem>
                  <MenuItem value="digital_payment">Digital Payment</MenuItem>
                </Select>
              </FormControl>

              {formData.payment_method === "digital_payment" && (
                <FormControl fullWidth required>
                  <InputLabel>Payment Gateway</InputLabel>
                  <Select
                    value={formData.payment_gateway}
                    label="Payment Gateway"
                    onChange={(e) => setFormData({ ...formData, payment_gateway: e.target.value })}
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
                type="submit"
                variant="contained"
                color="primary"
                size="large"
                disabled={isLoading}
              >
                {isLoading ? <CircularProgress size={20} /> : "Purchase"}
              </Button>
            </CustomStackFullWidth>
          </form>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default GiftCardPurchase;

