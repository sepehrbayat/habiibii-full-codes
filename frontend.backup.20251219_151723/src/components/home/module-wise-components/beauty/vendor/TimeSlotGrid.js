import React from "react";
import { Box, Typography, Grid, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const TimeSlotGrid = ({ availability, date, onRefetch }) => {
  const timeSlots = availability?.time_slots || [];
  const blocks = availability?.blocks || [];

  if (timeSlots.length === 0) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="body1" color="text.secondary">
          No availability data for this date
        </Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={2}>
      <Typography variant="h6">Available Time Slots - {date}</Typography>
      <Grid container spacing={2}>
        {timeSlots.map((slot, index) => {
          const isBlocked = blocks.some(
            (block) =>
              block.start_time <= slot.time && block.end_time > slot.time
          );
          return (
            <Grid item xs={6} sm={4} md={3} key={index}>
              <Chip
                label={slot.time}
                color={isBlocked ? "error" : slot.available ? "success" : "default"}
                variant={slot.available && !isBlocked ? "filled" : "outlined"}
              />
            </Grid>
          );
        })}
      </Grid>
    </CustomStackFullWidth>
  );
};

export default TimeSlotGrid;

