import React, { useState } from "react";
import { Typography, Box, Tabs, Tab, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import VendorBookingCard from "./VendorBookingCard";
import { useGetVendorBookings } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorBookings";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";

const VendorBookingList = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();
  const [tab, setTab] = useState(0);
  const [status, setStatus] = useState("all");

  const { data, isLoading, refetch } = useGetVendorBookings(
    {
      all: status,
      limit: 25,
      offset: 0,
    },
    !!vendorToken
  );

  const handleTabChange = (event, newValue) => {
    setTab(newValue);
    if (newValue === 0) {
      setStatus("all");
    } else if (newValue === 1) {
      setStatus("pending");
    } else if (newValue === 2) {
      setStatus("confirmed");
    } else if (newValue === 3) {
      setStatus("completed");
    } else if (newValue === 4) {
      setStatus("cancelled");
    }
  };

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view bookings</Typography>
      </Box>
    );
  }

  const bookings = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Bookings
      </Typography>

      <Tabs value={tab} onChange={handleTabChange}>
        <Tab label="All" />
        <Tab label="Pending" />
        <Tab label="Confirmed" />
        <Tab label="Completed" />
        <Tab label="Cancelled" />
      </Tabs>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : bookings.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {bookings.map((booking) => (
            <VendorBookingCard
              key={booking.id}
              booking={booking}
              onViewDetails={(id) => router.push(`/beauty/vendor/bookings/${id}`)}
              onRefetch={refetch}
            />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No bookings found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default VendorBookingList;

