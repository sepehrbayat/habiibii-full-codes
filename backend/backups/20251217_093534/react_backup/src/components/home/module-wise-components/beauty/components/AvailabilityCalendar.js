import React, { useEffect, useRef } from "react";
import {
  Typography,
  Box,
  Grid,
  Button,
  Card,
  CardContent,
  CircularProgress,
  Chip,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useCheckAvailability } from "../../../../../api-manage/hooks/react-query/beauty/useCheckAvailability";
import { DatePicker } from "@mui/x-date-pickers/DatePicker";
import { LocalizationProvider } from "@mui/x-date-pickers/LocalizationProvider";
import { AdapterDayjs } from "@mui/x-date-pickers/AdapterDayjs";
import dayjs from "dayjs";

const AvailabilityCalendar = ({
  salonId,
  serviceId,
  staffId,
  selectedDate,
  selectedTime,
  onDateSelect,
  onTimeSelect,
}) => {
  const { mutate: checkAvailability, data: availabilityData, isLoading } = useCheckAvailability();
  const checkedDatesRef = useRef({});

  // Clear cached checks when the context (salon/service) changes
  useEffect(() => {
    checkedDatesRef.current = {};
    if (onDateSelect) onDateSelect(null);
    if (onTimeSelect) onTimeSelect("");
  }, [salonId, serviceId, staffId]);

  useEffect(() => {
    if (selectedDate && salonId && serviceId) {
      const dateStr = selectedDate.format("YYYY-MM-DD");
      const cacheKey = `${dateStr}-${salonId}-${serviceId}-${staffId || "any"}`;
      if (!checkedDatesRef.current[cacheKey]) {
        checkAvailability({
          salon_id: salonId,
          service_id: serviceId,
          date: dateStr,
          staff_id: staffId || undefined,
        });
        checkedDatesRef.current[cacheKey] = true;
      }
    }
    // Only re-run when external dependencies change
  }, [selectedDate, salonId, serviceId, staffId, checkAvailability]);

  const availableSlots = availabilityData?.data?.available_slots || [];

  const handleDateChange = (date) => {
    if (onDateSelect) {
      onDateSelect(date ? dayjs(date) : null);
    }
    if (onTimeSelect) {
      onTimeSelect("");
    }
  };

  const handleTimeSelect = (time) => {
    if (onTimeSelect) {
      onTimeSelect(time);
    }
  };

  if (!salonId || !serviceId) {
    return (
      <Box p={2}>
        <Typography variant="body2" color="text.secondary">
          Please select a salon and service to view availability
        </Typography>
      </Box>
    );
  }

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <CustomStackFullWidth spacing={3}>
        <Typography variant="h6" fontWeight="bold">
          Select Date & Time
        </Typography>

        <DatePicker
          label="Booking Date"
          value={selectedDate}
          onChange={handleDateChange}
          minDate={dayjs()}
          slotProps={{
            textField: {
              fullWidth: true,
              required: true,
            },
          }}
        />

        {selectedDate && (
          <Card>
            <CardContent>
              <CustomStackFullWidth spacing={2}>
                <Typography variant="subtitle1" fontWeight="bold">
                  Available Time Slots for {selectedDate.format("MMMM DD, YYYY")}
                </Typography>

                {isLoading ? (
                  <Box display="flex" justifyContent="center" p={2}>
                    <CircularProgress size={24} />
                  </Box>
                ) : availableSlots.length > 0 ? (
                  <Box display="flex" flexWrap="wrap" gap={1}>
                    {availableSlots.map((slot) => {
                      const isSelected = selectedTime === slot;
                      return (
                        <Chip
                          key={slot}
                          label={slot}
                          onClick={() => handleTimeSelect(slot)}
                          color={isSelected ? "primary" : "default"}
                          variant={isSelected ? "filled" : "outlined"}
                          sx={{ cursor: "pointer" }}
                        />
                      );
                    })}
                  </Box>
                ) : (
                  <Typography variant="body2" color="text.secondary">
                    No available slots for this date
                  </Typography>
                )}
              </CustomStackFullWidth>
            </CardContent>
          </Card>
        )}
      </CustomStackFullWidth>
    </LocalizationProvider>
  );
};

export default AvailabilityCalendar;

