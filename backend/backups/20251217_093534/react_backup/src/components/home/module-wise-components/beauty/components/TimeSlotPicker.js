import React from "react";
import { Box, Button, Typography } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const TimeSlotPicker = ({ slots, value, onChange }) => {
  return (
    <CustomStackFullWidth spacing={2}>
      <Typography variant="subtitle1" fontWeight="bold">
        Available Time Slots
      </Typography>
      <Box display="flex" flexWrap="wrap" gap={1}>
        {slots.map((slot) => (
          <Button
            key={slot}
            variant={value === slot ? "contained" : "outlined"}
            color={value === slot ? "primary" : "inherit"}
            onClick={() => onChange(slot)}
            sx={{ minWidth: 100 }}
          >
            {slot}
          </Button>
        ))}
      </Box>
      {slots.length === 0 && (
        <Typography variant="body2" color="text.secondary">
          No available slots for this date
        </Typography>
      )}
    </CustomStackFullWidth>
  );
};

export default TimeSlotPicker;

