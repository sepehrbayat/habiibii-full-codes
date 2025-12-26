import React from "react";
import { Grid, Card, CardContent, Typography, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetVendorBookings } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorBookings";
import { getVendorToken } from "helper-functions/getToken";

const DashboardStats = () => {
  const vendorToken = getVendorToken();
  
  // Get all bookings to calculate stats
  const { data: allBookings, isLoading } = useGetVendorBookings(
    {
      all: "all",
      limit: 1000,
      offset: 0,
    },
    !!vendorToken
  );

  const bookings = allBookings?.data || [];
  
  const stats = {
    total: bookings.length,
    pending: bookings.filter((b) => b.status === "pending").length,
    confirmed: bookings.filter((b) => b.status === "confirmed").length,
    completed: bookings.filter((b) => b.status === "completed").length,
    cancelled: bookings.filter((b) => b.status === "cancelled").length,
    totalRevenue: bookings
      .filter((b) => b.status === "completed" && b.payment_status === "paid")
      .reduce((sum, b) => sum + (parseFloat(b.total_amount) || 0), 0),
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  const statCards = [
    { label: "Total Bookings", value: stats.total, color: "primary" },
    { label: "Pending", value: stats.pending, color: "warning" },
    { label: "Confirmed", value: stats.confirmed, color: "info" },
    { label: "Completed", value: stats.completed, color: "success" },
    { label: "Cancelled", value: stats.cancelled, color: "error" },
    { label: "Total Revenue", value: `$${stats.totalRevenue.toFixed(2)}`, color: "success" },
  ];

  return (
    <CustomStackFullWidth>
      <Typography variant="h4" fontWeight="bold" gutterBottom>
        Dashboard Overview
      </Typography>
      <Grid container spacing={3}>
        {statCards.map((stat, index) => (
          <Grid item xs={12} sm={6} md={4} key={index}>
            <Card>
              <CardContent>
                <Typography variant="body2" color="text.secondary" gutterBottom>
                  {stat.label}
                </Typography>
                <Typography variant="h4" color={`${stat.color}.main`} fontWeight="bold">
                  {stat.value}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </CustomStackFullWidth>
  );
};

export default DashboardStats;

