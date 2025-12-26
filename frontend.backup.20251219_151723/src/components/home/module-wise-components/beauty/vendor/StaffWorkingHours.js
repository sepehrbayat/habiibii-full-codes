import React from "react";
import { Typography, Box } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const StaffWorkingHours = ({ workingHours }) => {
  if (!workingHours || !Array.isArray(workingHours) || workingHours.length === 0) {
    return (
      <Box>
        <Typography variant="body2" color="text.secondary">
          No working hours set
        </Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={1}>
      <Typography variant="subtitle2" fontWeight="bold">
        Working Hours
      </Typography>
      {workingHours.map((wh, index) => (
        <Typography key={index} variant="body2" color="text.secondary">
          {wh.day}: {wh.open} - {wh.close}
        </Typography>
      ))}
    </CustomStackFullWidth>
  );
};

export default StaffWorkingHours;

