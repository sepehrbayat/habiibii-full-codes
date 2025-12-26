import React, { useMemo } from "react";
import {
  Box,
  Card,
  CardContent,
  CircularProgress,
  Grid,
  Typography,
  Button,
  Divider,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetBookings from "../../../../../api-manage/hooks/react-query/beauty/useGetBookings";
import { useQuery } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import { useRouter } from "next/router";

const SummaryCard = ({ title, value, loading }) => (
  <Card>
    <CardContent>
      <Typography variant="subtitle2" color="text.secondary">
        {title}
      </Typography>
      {loading ? (
        <Box display="flex" justifyContent="center" py={1}>
          <CircularProgress size={18} />
        </Box>
      ) : (
        <Typography variant="h5" fontWeight="bold">
          {value}
        </Typography>
      )}
    </CardContent>
  </Card>
);

const BeautyDashboard = () => {
  const router = useRouter();

  const {
    data: upcomingData,
    isLoading: loadingUpcoming,
  } = useGetBookings({ limit: 5, offset: 0, type: "upcoming" }, true);

  const { data: summaryData, isLoading: loadingSummary } = useQuery(
    ["beauty-dashboard-summary"],
    async () => {
      const { data } = await BeautyApi.getDashboardSummary();
      return data?.data || data;
    }
  );

  const upcomingBookings = upcomingData?.data || [];
  const totalBookings = summaryData?.total_bookings || 0;
  const loyaltyPoints = summaryData?.loyalty_points || 0;
  const giftCardBalance = summaryData?.gift_card_balance || 0;
  const packagesCount = summaryData?.active_packages || 0;
  const pendingConsultations = summaryData?.pending_consultations || 0;
  const totalSpent = summaryData?.total_spent || 0;

  const recentActivity = useMemo(() => {
    const bookings = upcomingData?.data || [];
    return bookings.slice(0, 5);
  }, [upcomingData]);

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Beauty Dashboard
      </Typography>

      <Grid container spacing={2}>
        <Grid item xs={12} md={3}>
          <SummaryCard
            title="Upcoming Bookings"
            value={upcomingBookings.length}
            loading={loadingUpcoming}
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <SummaryCard
            title="Total Bookings"
            value={totalBookings}
            loading={loadingSummary}
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <SummaryCard
            title="Loyalty Points"
            value={loyaltyPoints}
            loading={loadingSummary}
          />
        </Grid>
        <Grid item xs={12} md={3}>
          <SummaryCard
            title="Gift Card Balance"
            value={`$${Number(giftCardBalance || 0).toFixed(2)}`}
            loading={loadingSummary}
          />
        </Grid>
      </Grid>

      <Grid container spacing={2}>
        <Grid item xs={12} md={4}>
          <SummaryCard
            title="Total Spent"
            value={`$${Number(totalSpent || 0).toFixed(2)}`}
            loading={loadingSummary}
          />
        </Grid>
        <Grid item xs={12} md={4}>
          <SummaryCard
            title="Pending Consultations"
            value={pendingConsultations}
            loading={loadingSummary}
          />
        </Grid>
        <Grid item xs={12} md={4}>
          <SummaryCard
            title="Active Packages"
            value={packagesCount}
            loading={loadingSummary}
          />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid item xs={12} md={8}>
          <Card>
            <CardContent>
              <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
                <Typography variant="h6" fontWeight="bold">
                  Upcoming Bookings
                </Typography>
                <Button onClick={() => router.push("/beauty/bookings")} size="small">
                  View All
                </Button>
              </Box>
              {loadingUpcoming ? (
                <Box display="flex" justifyContent="center" py={3}>
                  <CircularProgress />
                </Box>
              ) : upcomingBookings.length > 0 ? (
                <CustomStackFullWidth spacing={2}>
                  {upcomingBookings.map((booking) => (
                    <Box
                      key={booking.id}
                      sx={{
                        p: 2,
                        border: "1px solid",
                        borderColor: "divider",
                        borderRadius: 1,
                      }}
                    >
                      <Typography variant="subtitle1" fontWeight="bold">
                        {booking.service_name || "Service"} @ {booking.salon_name || "Salon"}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        {booking.booking_date} at {booking.booking_time}
                      </Typography>
                    </Box>
                  ))}
                </CustomStackFullWidth>
              ) : (
                <Typography variant="body2" color="text.secondary">
                  No upcoming bookings
                </Typography>
              )}
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Typography variant="h6" fontWeight="bold" gutterBottom>
                Quick Actions
              </Typography>
              <CustomStackFullWidth spacing={1.5}>
                <Button variant="contained" onClick={() => router.push("/beauty/booking/create")}>
                  Create Booking
                </Button>
                <Button variant="outlined" onClick={() => router.push("/beauty/packages")}>
                  View Packages
                </Button>
                <Button variant="outlined" onClick={() => router.push("/beauty/loyalty")}>
                  Loyalty & Rewards
                </Button>
                <Button variant="outlined" onClick={() => router.push("/beauty/retail/products")}>
                  Shop Retail
                </Button>
              </CustomStackFullWidth>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Card>
        <CardContent>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
            <Typography variant="h6" fontWeight="bold">
              Recent Activity
            </Typography>
          </Box>
          <Divider />
          {recentActivity.length > 0 ? (
            <CustomStackFullWidth spacing={2} mt={2}>
              {recentActivity.map((item) => (
                <Box key={item.id} display="flex" justifyContent="space-between">
                  <Box>
                    <Typography variant="subtitle2" fontWeight="bold">
                      {item.service_name || "Service"}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                      {item.booking_date} {item.booking_time}
                    </Typography>
                  </Box>
                  <Typography variant="body2" color="text.secondary">
                    {item.status}
                  </Typography>
                </Box>
              ))}
            </CustomStackFullWidth>
          ) : (
            <Box py={2}>
              <Typography variant="body2" color="text.secondary">
                No recent activity yet
              </Typography>
            </Box>
          )}
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default BeautyDashboard;

