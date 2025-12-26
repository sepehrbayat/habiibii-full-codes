import React from "react";
import { Card, CardContent, Typography, Box, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const RetailOrderCard = ({ order }) => {
  const getStatusColor = (status) => {
    switch (status) {
      case "pending":
        return "warning";
      case "confirmed":
        return "info";
      case "completed":
        return "success";
      case "cancelled":
        return "error";
      default:
        return "default";
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                Order #{order.id}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Customer: {order.user?.name || order.user?.email || "N/A"}
              </Typography>
            </Box>
            <Chip
              label={order.status || "pending"}
              color={getStatusColor(order.status)}
              size="small"
            />
          </Box>

          <Box>
            <Typography variant="body2" color="text.secondary">
              Total Amount: ${order.total_amount || 0}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Date: {order.created_at || "N/A"}
            </Typography>
          </Box>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default RetailOrderCard;

