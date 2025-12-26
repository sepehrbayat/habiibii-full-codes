import React from "react";
import {
  Typography,
  Box,
  Card,
  CardContent,
  Chip,
  CircularProgress,
  Divider,
  Grid,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetRetailOrderDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetRetailOrderDetails";
import { useRouter } from "next/router";

const RetailOrderDetails = ({ orderId }) => {
  const router = useRouter();
  const { data, isLoading } = useGetRetailOrderDetails(orderId, !!orderId);
  const order = data?.data || data;

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (!order) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Order not found</Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Retail Order Details
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={2}>
            <Box display="flex" justifyContent="space-between" alignItems="flex-start" flexWrap="wrap" gap={2}>
              <Box>
                <Typography variant="h6" fontWeight="bold">
                  {order.order_reference || `Order #${order.id}`}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {order.created_at || order.order_date || "Date not available"}
                </Typography>
                {order.salon && (
                  <Typography variant="body2" color="text.secondary">
                    Salon: {order.salon.name || order.salon_name}
                  </Typography>
                )}
              </Box>
              <CustomStackFullWidth spacing={1} sx={{ maxWidth: 220 }}>
                <Chip
                  label={order.status || "pending"}
                  color={
                    order.status === "completed"
                      ? "success"
                      : order.status === "cancelled"
                      ? "error"
                      : "warning"
                  }
                  size="small"
                />
                <Chip
                  label={order.payment_status || "unpaid"}
                  color={order.payment_status === "paid" ? "success" : "warning"}
                  size="small"
                />
              </CustomStackFullWidth>
            </Box>

            <Divider />

            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Total Amount
              </Typography>
              <Typography variant="h6" color="primary" fontWeight="bold">
                ${order.total_amount || order.total || 0}
              </Typography>
            </Box>

            {order.payment_method && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Payment Method
                </Typography>
                <Typography variant="body1" fontWeight="bold">
                  {order.payment_method}
                </Typography>
              </Box>
            )}

            {order.products && order.products.length > 0 && (
              <Box>
                <Typography variant="h6" fontWeight="bold" gutterBottom>
                  Products
                </Typography>
                <Grid container spacing={2}>
                  {order.products.map((product) => (
                    <Grid item xs={12} sm={6} key={product.id || product.product_id}>
                      <Card variant="outlined">
                        <CardContent>
                          <Typography variant="subtitle1" fontWeight="bold">
                            {product.name || product.product_name}
                          </Typography>
                          <Typography variant="body2" color="text.secondary">
                            Qty: {product.quantity}
                          </Typography>
                          <Typography variant="body2" color="text.secondary">
                            Price: ${product.price}
                          </Typography>
                        </CardContent>
                      </Card>
                    </Grid>
                  ))}
                </Grid>
              </Box>
            )}

            {order.shipping_address && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Shipping Address
                </Typography>
                <Typography variant="body1">{order.shipping_address}</Typography>
              </Box>
            )}

            {order.notes && (
              <Box>
                <Typography variant="subtitle2" color="text.secondary">
                  Notes
                </Typography>
                <Typography variant="body1">{order.notes}</Typography>
              </Box>
            )}
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default RetailOrderDetails;

