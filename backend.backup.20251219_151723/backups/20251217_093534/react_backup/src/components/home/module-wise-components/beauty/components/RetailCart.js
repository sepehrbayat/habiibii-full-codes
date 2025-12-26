import React, { useState, useEffect } from "react";
import { Typography, Box, Button, Card, CardContent, IconButton, TextField, Divider } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import DeleteIcon from "@mui/icons-material/Delete";
import { useRouter } from "next/router";
import { getImageUrl } from "utils/CustomFunctions";
import { useSelector } from "react-redux";

const RetailCart = ({ configData }) => {
  const router = useRouter();
  const [cart, setCart] = useState([]);

  useEffect(() => {
    const storedCart = JSON.parse(localStorage.getItem("beauty_retail_cart") || "[]");
    setCart(storedCart);
  }, []);

  const updateCart = (newCart) => {
    setCart(newCart);
    localStorage.setItem("beauty_retail_cart", JSON.stringify(newCart));
  };

  const handleQuantityChange = (productId, newQuantity) => {
    if (newQuantity < 1) {
      handleRemoveItem(productId);
      return;
    }
    const updatedCart = cart.map((item) =>
      item.product_id === productId ? { ...item, quantity: newQuantity } : item
    );
    updateCart(updatedCart);
  };

  const handleRemoveItem = (productId) => {
    const updatedCart = cart.filter((item) => item.product_id !== productId);
    updateCart(updatedCart);
  };

  const totalAmount = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

  const handleProceedToCheckout = () => {
    router.push("/beauty/retail/checkout");
  };

  if (cart.length === 0) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6" gutterBottom>
          Your cart is empty
        </Typography>
        <Button
          variant="contained"
          color="primary"
          onClick={() => router.back()}
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
        Shopping Cart
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={3}>
            {cart.map((item) => (
              <Box key={item.product_id}>
                <Box display="flex" gap={2} alignItems="center">
                  {item.image && (
                    <Box
                      component="img"
                      src={getImageUrl({ value: item.image }, "product_image_url", configData)}
                      alt={item.product_name}
                      sx={{ width: 80, height: 80, objectFit: "cover", borderRadius: 1 }}
                    />
                  )}
                  <Box flexGrow={1}>
                    <Typography variant="h6">{item.product_name}</Typography>
                    <Typography variant="body2" color="text.secondary">
                      ${item.price} each
                    </Typography>
                  </Box>
                  <Box display="flex" alignItems="center" gap={1}>
                    <TextField
                      type="number"
                      value={item.quantity}
                      onChange={(e) =>
                        handleQuantityChange(item.product_id, parseInt(e.target.value) || 1)
                      }
                      inputProps={{ min: 1 }}
                      sx={{ width: 80 }}
                      size="small"
                    />
                    <Typography variant="h6" sx={{ minWidth: 100, textAlign: "right" }}>
                      ${(item.price * item.quantity).toFixed(2)}
                    </Typography>
                    <IconButton
                      color="error"
                      onClick={() => handleRemoveItem(item.product_id)}
                    >
                      <DeleteIcon />
                    </IconButton>
                  </Box>
                </Box>
                <Divider sx={{ mt: 2 }} />
              </Box>
            ))}

            <Box display="flex" justifyContent="space-between" alignItems="center" sx={{ pt: 2 }}>
              <Typography variant="h5" fontWeight="bold">
                Total: ${totalAmount.toFixed(2)}
              </Typography>
              <Button
                variant="contained"
                color="primary"
                size="large"
                onClick={handleProceedToCheckout}
              >
                Proceed to Checkout
              </Button>
            </Box>
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default RetailCart;

