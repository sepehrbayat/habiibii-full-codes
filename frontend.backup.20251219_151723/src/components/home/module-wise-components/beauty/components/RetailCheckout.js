import React, { useState, useEffect } from "react";
import {
  Typography,
  Box,
  TextField,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Card,
  CardContent,
  Divider,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { useCreateRetailOrder } from "../../../../../api-manage/hooks/react-query/beauty/useCreateRetailOrder";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { getImageUrl } from "utils/CustomFunctions";
import { useSelector } from "react-redux";

const RetailCheckout = () => {
  const router = useRouter();
  const { salon_id } = router.query;
  const { configData } = useSelector((state) => state.configData);
  const { mutate: createOrder, isLoading } = useCreateRetailOrder();

  const [cart, setCart] = useState([]);
  const [formData, setFormData] = useState({
    shipping_address: "",
    shipping_phone: "",
    shipping_fee: 0,
    discount: 0,
    payment_method: "cash_payment",
    payment_gateway: "",
  });

  useEffect(() => {
    const storedCart = JSON.parse(localStorage.getItem("beauty_retail_cart") || "[]");
    setCart(storedCart);
  }, []);

  const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  const total = subtotal + (formData.shipping_fee || 0) - (formData.discount || 0);

  const convertPaymentMethod = (value) =>
    value === "online" ? "digital_payment" : value;

  const digitalGateways =
    configData?.active_payment_method_list?.filter(
      (method) => method?.type === "digital_payment"
    ) || [];

  const hasShippingContact = () =>
    !!formData.shipping_address || !!formData.shipping_phone;

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!salon_id) {
      toast.error("Salon ID is required");
      return;
    }

    if (cart.length === 0) {
      toast.error("Cart is empty");
      return;
    }

    if (hasShippingContact() && !formData.shipping_phone) {
      toast.error("Please provide a phone number for shipping");
      return;
    }

    // Convert 'online' to 'digital_payment' for Laravel compatibility
    const paymentMethod = convertPaymentMethod(formData.payment_method);

    if (paymentMethod === "digital_payment" && !formData.payment_gateway) {
      toast.error("Please select a payment gateway");
      return;
    }

    const orderData = {
      salon_id: parseInt(salon_id),
      items: cart.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
      payment_method: paymentMethod,
    };

    if (paymentMethod === "digital_payment" && formData.payment_gateway) {
      orderData.payment_gateway = formData.payment_gateway;
    }

    if (formData.shipping_address) {
      orderData.shipping_address = formData.shipping_address;
    }
    if (formData.shipping_phone) {
      orderData.shipping_phone = formData.shipping_phone;
    }
    if (formData.shipping_fee) {
      orderData.shipping_fee = parseFloat(formData.shipping_fee);
    }
    if (formData.discount) {
      orderData.discount = parseFloat(formData.discount);
    }

    createOrder(orderData, {
      onSuccess: (response) => {
        toast.success("Order created successfully");
        localStorage.removeItem("beauty_retail_cart");
        if (response?.data?.redirect_url) {
          window.location.href = response.data.redirect_url;
        } else {
          router.push(`/beauty/bookings/${response?.data?.id || response?.data?.order?.id}`);
        }
      },
      onError: (error) => {
        toast.error(getBeautyErrorMessage(error) || "Failed to create order");
      },
    });
  };

  if (cart.length === 0) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Your cart is empty</Typography>
        <Button
          variant="contained"
          color="primary"
          onClick={() => router.push("/beauty/retail/products")}
          sx={{ mt: 2 }}
        >
          Continue Shopping
        </Button>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Checkout
      </Typography>

      <Box display="flex" flexDirection={{ xs: "column", md: "row" }} gap={3}>
        <Card sx={{ flex: 1 }}>
          <CardContent>
            <form onSubmit={handleSubmit}>
              <CustomStackFullWidth spacing={3}>
                <Typography variant="h6" fontWeight="bold">
                  Shipping Information (Optional)
                </Typography>

                <TextField
                  fullWidth
                  label="Shipping Address"
                  value={formData.shipping_address}
                  onChange={(e) => setFormData({ ...formData, shipping_address: e.target.value })}
                />

                <TextField
                  fullWidth
                  label="Shipping Phone"
                  value={formData.shipping_phone}
                  onChange={(e) => setFormData({ ...formData, shipping_phone: e.target.value })}
                />

                <TextField
                  fullWidth
                  type="number"
                  label="Shipping Fee (Optional)"
                  value={formData.shipping_fee}
                  onChange={(e) => setFormData({ ...formData, shipping_fee: e.target.value })}
                  inputProps={{ min: 0, step: 0.01 }}
                />

                <TextField
                  fullWidth
                  type="number"
                  label="Discount (Optional)"
                  value={formData.discount}
                  onChange={(e) => setFormData({ ...formData, discount: e.target.value })}
                  inputProps={{ min: 0, step: 0.01 }}
                />

                <FormControl fullWidth required>
                  <InputLabel>Payment Method</InputLabel>
                  <Select
                    value={formData.payment_method}
                    label="Payment Method"
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        payment_method: e.target.value,
                        // reset gateway when switching away from digital
                        payment_gateway:
                          e.target.value === "digital_payment"
                            ? formData.payment_gateway
                            : "",
                      })
                    }
                  >
                    <MenuItem value="cash_payment">Cash on Delivery</MenuItem>
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
                      onChange={(e) =>
                        setFormData({ ...formData, payment_gateway: e.target.value })
                      }
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
                  fullWidth
                  disabled={isLoading}
                >
                  {isLoading ? "Processing..." : "Place Order"}
                </Button>
              </CustomStackFullWidth>
            </form>
          </CardContent>
        </Card>

        <Card sx={{ flex: 1, maxWidth: { md: 400 } }}>
          <CardContent>
            <CustomStackFullWidth spacing={2}>
              <Typography variant="h6" fontWeight="bold">
                Order Summary
              </Typography>

              <Divider />

              {cart.map((item) => (
                <Box key={item.product_id} display="flex" justifyContent="space-between">
                  <Box>
                    <Typography variant="body1">{item.product_name}</Typography>
                    <Typography variant="body2" color="text.secondary">
                      {item.quantity} x ${item.price}
                    </Typography>
                  </Box>
                  <Typography variant="body1" fontWeight="bold">
                    ${(item.price * item.quantity).toFixed(2)}
                  </Typography>
                </Box>
              ))}

              <Divider />

              <Box display="flex" justifyContent="space-between">
                <Typography variant="body1">Subtotal</Typography>
                <Typography variant="body1">${subtotal.toFixed(2)}</Typography>
              </Box>

              {formData.shipping_fee > 0 && (
                <Box display="flex" justifyContent="space-between">
                  <Typography variant="body1">Shipping Fee</Typography>
                  <Typography variant="body1">${parseFloat(formData.shipping_fee).toFixed(2)}</Typography>
                </Box>
              )}

              {formData.discount > 0 && (
                <Box display="flex" justifyContent="space-between">
                  <Typography variant="body1">Discount</Typography>
                  <Typography variant="body1" color="error">
                    -${parseFloat(formData.discount).toFixed(2)}
                  </Typography>
                </Box>
              )}

              <Divider />

              <Box display="flex" justifyContent="space-between">
                <Typography variant="h6" fontWeight="bold">
                  Total
                </Typography>
                <Typography variant="h6" fontWeight="bold" color="primary">
                  ${total.toFixed(2)}
                </Typography>
              </Box>
            </CustomStackFullWidth>
          </CardContent>
        </Card>
      </Box>
    </CustomStackFullWidth>
  );
};

export default RetailCheckout;

