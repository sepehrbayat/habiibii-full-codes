import React from "react";
import { Typography, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import VendorBookingCard from "./VendorBookingCard";
import { useGetVendorBookings } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorBookings";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";

const RecentBookings = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();

  const { data, isLoading } = useGetVendorBookings(
    {
      all: "all",
      limit: 5,
      offset: 0,
    },
    !!vendorToken
  );

  const bookings = data?.data || [];

  return (
    <CustomStackFullWidth spacing={2}>
      <Typography variant="h5" fontWeight="bold">
        Recent Bookings
      </Typography>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : bookings.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {bookings.slice(0, 5).map((booking) => (
            <VendorBookingCard
              key={booking.id}
              booking={booking}
              onViewDetails={(id) => router.push(`/beauty/vendor/bookings/${id}`)}
            />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No recent bookings
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RecentBookings;

