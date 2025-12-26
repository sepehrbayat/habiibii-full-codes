import React, { useMemo, useState } from "react";
import { Typography, Box, Tabs, Tab, CircularProgress, TextField, Grid, Pagination, Alert, FormControl, InputLabel, Select, MenuItem } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import BookingCard from "./BookingCard";
import useGetBookings from "../../../../../api-manage/hooks/react-query/beauty/useGetBookings";
import { getToken } from "helper-functions/getToken";
import { useRouter } from "next/router";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";

const BookingList = () => {
  const token = getToken();
  const router = useRouter();
  const [tab, setTab] = useState(0);
  const [type, setType] = useState("");
  const [status, setStatus] = useState("");
  const [serviceType, setServiceType] = useState("");
  const [staffId, setStaffId] = useState("");
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [page, setPage] = useState(1);
  const [sort, setSort] = useState("latest");
  const limit = 10;
  const offset = (page - 1) * limit;

  const dateRange = useMemo(() => {
    if (dateFrom && dateTo) return `${dateFrom},${dateTo}`;
    return "";
  }, [dateFrom, dateTo]);

  const { data, isLoading, isError, error, refetch } = useGetBookings(
    {
      limit,
      offset,
      type: type || undefined,
      status: status || undefined,
      service_type: serviceType || undefined,
      staff_id: staffId || undefined,
      date_range: dateRange || undefined,
    },
    !!token
  );

  const handleTabChange = (event, newValue) => {
    setTab(newValue);
    setPage(1);
    if (newValue === 0) {
      setType("");
      setStatus("");
    } else if (newValue === 1) {
      setType("upcoming");
      setStatus("");
    } else if (newValue === 2) {
      setType("past");
      setStatus("");
    } else if (newValue === 3) {
      setType("");
      setStatus("cancelled");
    }
  };

  const totalPages = useMemo(() => {
    if (!data?.total) return 1;
    return Math.max(1, Math.ceil(data.total / limit));
  }, [data?.total, limit]);

  const bookings = data?.data || [];
  const uniqueServices = useMemo(
    () => Array.from(new Map(bookings.map((b) => [b.service_id, b.service_name])).entries()),
    [bookings]
  );
  const uniqueStaff = useMemo(
    () => Array.from(new Map(bookings.map((b) => [b.staff_id, b.staff_name || b.staff?.name])).entries()),
    [bookings]
  );

  const sortedBookings = useMemo(() => {
    const getDateValue = (booking) => {
      const dt = booking.booking_date
        ? new Date(`${booking.booking_date} ${booking.booking_time || "00:00"}`)
        : null;
      return dt ? dt.getTime() : 0;
    };

    const copy = [...bookings];
    switch (sort) {
      case "oldest":
        return copy.sort((a, b) => getDateValue(a) - getDateValue(b));
      case "amount_desc":
        return copy.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0));
      case "amount_asc":
        return copy.sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0));
      case "latest":
      default:
        return copy.sort((a, b) => getDateValue(b) - getDateValue(a));
    }
  }, [bookings, sort]);

  if (!token) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view your bookings</Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        My Bookings
      </Typography>

      <Tabs value={tab} onChange={handleTabChange}>
        <Tab label="All" />
        <Tab label="Upcoming" />
        <Tab label="Past" />
        <Tab label="Cancelled" />
      </Tabs>

      <Grid container spacing={2}>
        <Grid item xs={12} md={4}>
          <FormControl fullWidth>
            <InputLabel>Service</InputLabel>
            <Select
              value={serviceType}
              label="Service"
              onChange={(e) => setServiceType(e.target.value)}
            >
              <MenuItem value="">All</MenuItem>
              {uniqueServices
                .filter(([id]) => id)
                .map(([id, name]) => (
                  <MenuItem key={id} value={id}>
                    {name || `Service ${id}`}
                  </MenuItem>
                ))}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12} md={4}>
          <FormControl fullWidth>
            <InputLabel>Staff</InputLabel>
            <Select
              value={staffId}
              label="Staff"
              onChange={(e) => setStaffId(e.target.value)}
            >
              <MenuItem value="">All</MenuItem>
              {uniqueStaff
                .filter(([id]) => id)
                .map(([id, name]) => (
                  <MenuItem key={id} value={id}>
                    {name || `Staff ${id}`}
                  </MenuItem>
                ))}
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={6} md={2}>
          <TextField
            fullWidth
            type="date"
            label="From"
            InputLabelProps={{ shrink: true }}
            value={dateFrom}
            onChange={(e) => {
              setDateFrom(e.target.value);
              setPage(1);
            }}
          />
        </Grid>
        <Grid item xs={6} md={2}>
          <TextField
            fullWidth
            type="date"
            label="To"
            InputLabelProps={{ shrink: true }}
            value={dateTo}
            onChange={(e) => {
              setDateTo(e.target.value);
              setPage(1);
            }}
          />
        </Grid>
        <Grid item xs={12} md={2}>
          <FormControl fullWidth>
            <InputLabel>Status</InputLabel>
            <Select
              value={status}
              label="Status"
              onChange={(e) => {
                setStatus(e.target.value);
              }}
            >
              <MenuItem value="">All</MenuItem>
              <MenuItem value="confirmed">Confirmed</MenuItem>
              <MenuItem value="pending">Pending</MenuItem>
              <MenuItem value="cancelled">Cancelled</MenuItem>
              <MenuItem value="completed">Completed</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid item xs={12} md={2}>
          <FormControl fullWidth>
            <InputLabel>Sort</InputLabel>
            <Select
              value={sort}
              label="Sort"
              onChange={(e) => setSort(e.target.value)}
            >
              <MenuItem value="latest">Latest First</MenuItem>
              <MenuItem value="oldest">Oldest First</MenuItem>
              <MenuItem value="amount_desc">Amount High-Low</MenuItem>
              <MenuItem value="amount_asc">Amount Low-High</MenuItem>
            </Select>
          </FormControl>
        </Grid>
      </Grid>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : isError ? (
        <Alert severity="error">
          {getBeautyErrorMessage(error) || "Failed to load bookings"}
        </Alert>
      ) : sortedBookings.length > 0 ? (
        <>
          <CustomStackFullWidth spacing={2}>
            {sortedBookings.map((booking) => (
              <BookingCard
                key={booking.id}
                booking={booking}
                onViewDetails={(id) => router.push(`/beauty/bookings/${id}`)}
              />
            ))}
          </CustomStackFullWidth>

          {totalPages > 1 && (
            <Box display="flex" justifyContent="center" mt={2}>
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
            No bookings found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default BookingList;

