import React, { useState } from "react";
import { Typography, Box, Button, Select, MenuItem, FormControl, InputLabel } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import TimeSlotGrid from "./TimeSlotGrid";
import CalendarBlockForm from "./CalendarBlockForm";
import { useGetCalendarAvailability } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetCalendarAvailability";
import { getVendorToken } from "helper-functions/getToken";
import dayjs from "dayjs";

const CalendarView = () => {
  const vendorToken = getVendorToken();
  const [selectedDate, setSelectedDate] = useState(dayjs().format("YYYY-MM-DD"));
  const [staffId, setStaffId] = useState("");
  const [serviceId, setServiceId] = useState("");
  const [showBlockForm, setShowBlockForm] = useState(false);

  const { data, isLoading, refetch } = useGetCalendarAvailability(
    {
      date: selectedDate,
      staff_id: staffId || undefined,
      service_id: serviceId || undefined,
    },
    !!vendorToken
  );

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view calendar</Typography>
      </Box>
    );
  }

  const availability = data?.data || {};

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h4" fontWeight="bold">
          Calendar
        </Typography>
        <Button
          variant="contained"
          color="primary"
          onClick={() => setShowBlockForm(true)}
        >
          Add Block
        </Button>
      </Box>

      <Box display="flex" gap={2} flexWrap="wrap">
        <input
          type="date"
          value={selectedDate}
          onChange={(e) => setSelectedDate(e.target.value)}
        />
        <FormControl sx={{ minWidth: 200 }}>
          <InputLabel>Staff</InputLabel>
          <Select
            value={staffId}
            label="Staff"
            onChange={(e) => setStaffId(e.target.value)}
          >
            <MenuItem value="">All Staff</MenuItem>
            {/* Staff options would come from staff list */}
          </Select>
        </FormControl>
        <FormControl sx={{ minWidth: 200 }}>
          <InputLabel>Service</InputLabel>
          <Select
            value={serviceId}
            label="Service"
            onChange={(e) => setServiceId(e.target.value)}
          >
            <MenuItem value="">All Services</MenuItem>
            {/* Service options would come from service list */}
          </Select>
        </FormControl>
      </Box>

      <TimeSlotGrid
        availability={availability}
        date={selectedDate}
        onRefetch={refetch}
      />

      {showBlockForm && (
        <CalendarBlockForm
          open={showBlockForm}
          onClose={() => setShowBlockForm(false)}
          onSuccess={() => {
            setShowBlockForm(false);
            refetch();
          }}
        />
      )}
    </CustomStackFullWidth>
  );
};

export default CalendarView;

