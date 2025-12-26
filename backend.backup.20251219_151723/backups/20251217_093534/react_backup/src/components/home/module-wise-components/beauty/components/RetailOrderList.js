import React, { useState, useMemo } from "react";
import {
  Typography,
  Box,
  Card,
  CardContent,
  Chip,
  Button,
  CircularProgress,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Pagination,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetRetailOrders from "../../../../../api-manage/hooks/react-query/beauty/useGetRetailOrders";
import { useRouter } from "next/router";

const RetailOrderList = () => {
  const router = useRouter();
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;
  const offset = (page - 1) * limit;

  const { data, isLoading, refetch } = useGetRetailOrders(
    {
      limit,
      offset,
      status: status || undefined,
    },
    true
  );

  const orders = data?.data || [];
  const totalPages = useMemo(() => {
    if (!data?.total || !limit) return 1;
    return Math.max(1, Math.ceil(data.total / limit));
  }, [data?.total, limit]);

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center" flexWrap="wrap" gap={2}>
        <Typography variant="h4" fontWeight="bold">
          Retail Orders
        </Typography>
        <FormControl sx={{ minWidth: 180 }}>
          <InputLabel>Status</InputLabel>
          <Select
            label="Status"
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
          >
            <MenuItem value="">All</MenuItem>
            <MenuItem value="pending">Pending</MenuItem>
            <MenuItem value="processing">Processing</MenuItem>
            <MenuItem value="completed">Completed</MenuItem>
            <MenuItem value="cancelled">Cancelled</MenuItem>
          </Select>
        </FormControl>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : orders.length > 0 ? (
        <>
          <CustomStackFullWidth spacing={2}>
            {orders.map((order) => (
              <Card key={order.id}>
                <CardContent>
                  <Box display="flex" justifyContent="space-between" alignItems="flex-start" gap={2} flexWrap="wrap">
                    <Box>
                      <Typography variant="h6" fontWeight="bold">
                        {order.order_reference || `Order #${order.id}`}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Placed on {order.created_at || order.order_date || "-"}
                      </Typography>
                      <Typography variant="body1" fontWeight="bold" color="primary" mt={1}>
                        ${order.total_amount || order.total || 0}
                      </Typography>
                      {order.salon_name && (
                        <Typography variant="body2" color="text.secondary">
                          Salon: {order.salon_name}
                        </Typography>
                      )}
                    </Box>
                    <CustomStackFullWidth spacing={1} sx={{ maxWidth: 200 }}>
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
                      <Button
                        variant="outlined"
                        size="small"
                        onClick={() => router.push(`/beauty/retail/orders/${order.id}`)}
                      >
                        View Details
                      </Button>
                    </CustomStackFullWidth>
                  </Box>
                </CardContent>
              </Card>
            ))}
          </CustomStackFullWidth>

          {totalPages > 1 && (
            <Box display="flex" justifyContent="center">
              <Pagination
                count={totalPages}
                page={page}
                onChange={(event, value) => setPage(value)}
                color="primary"
              />
            </Box>
          )}
        </>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No retail orders found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RetailOrderList;
