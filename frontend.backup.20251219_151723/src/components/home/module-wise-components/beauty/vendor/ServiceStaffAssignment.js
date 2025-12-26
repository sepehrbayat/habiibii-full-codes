import React from "react";
import { Box, Typography, Checkbox, FormControlLabel, FormGroup } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const ServiceStaffAssignment = ({ staffList, selectedStaffIds, onChange }) => {
  const handleStaffToggle = (staffId) => {
    const currentIds = selectedStaffIds || [];
    const newIds = currentIds.includes(staffId)
      ? currentIds.filter((id) => id !== staffId)
      : [...currentIds, staffId];
    onChange(newIds);
  };

  if (!staffList || staffList.length === 0) {
    return (
      <Box>
        <Typography variant="body2" color="text.secondary">
          No staff members available
        </Typography>
      </Box>
    );
  }

  return (
    <Box>
      <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
        Assign Staff
      </Typography>
      <FormGroup>
        {staffList.map((staff) => (
          <FormControlLabel
            key={staff.id}
            control={
              <Checkbox
                checked={(selectedStaffIds || []).includes(staff.id)}
                onChange={() => handleStaffToggle(staff.id)}
              />
            }
            label={staff.name || `Staff #${staff.id}`}
          />
        ))}
      </FormGroup>
    </Box>
  );
};

export default ServiceStaffAssignment;

