import React from "react";
import { Typography, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import RetailOrderCard from "./RetailOrderCard";
import { useGetVendorRetailOrders } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorRetailOrders";
import { getVendorToken } from "helper-functions/getToken";

const RetailOrderList = () => {
  const vendorToken = getVendorToken();

  const { data, isLoading } = useGetVendorRetailOrders(
    {
      limit: 25,
      offset: 0,
    },
    !!vendorToken
  );

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view orders</Typography>
      </Box>
    );
  }

  const orders = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Retail Orders
      </Typography>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : orders.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {orders.map((order) => (
            <RetailOrderCard key={order.id} order={order} />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No orders found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RetailOrderList;

