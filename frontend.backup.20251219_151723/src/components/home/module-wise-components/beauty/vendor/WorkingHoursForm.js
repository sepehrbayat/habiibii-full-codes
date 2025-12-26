import React, { useState, useEffect } from "react";
import { Box, TextField, Button, Typography } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { toast } from "react-hot-toast";
import { useUpdateWorkingHours } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useUpdateWorkingHours";
import { useGetVendorProfile } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorProfile";

const daysOfWeek = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

const normalizeWorkingHours = (value) => {
  return daysOfWeek.map((day) => {
    const entry = Array.isArray(value)
      ? value.find((item) => item?.day === day)
      : value?.[day];
    return {
      day,
      open: entry?.open || "",
      close: entry?.close || "",
    };
  });
};

const WorkingHoursForm = () => {
  const { data: profileData } = useGetVendorProfile();
  const { mutate: updateWorkingHours, isLoading } = useUpdateWorkingHours();
  const [workingHours, setWorkingHours] = useState(() =>
    daysOfWeek.map((day) => ({ day, open: "", close: "" }))
  );

  useEffect(() => {
    if (profileData?.data?.working_hours) {
      setWorkingHours(normalizeWorkingHours(profileData.data.working_hours));
    }
  }, [profileData]);

  const handleChange = (index, field, value) => {
    const updated = [...workingHours];
    updated[index] = { ...updated[index], [field]: value };
    setWorkingHours(updated);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const hasEmpty = workingHours.some((item) => !item.open || !item.close);
    if (hasEmpty) {
      toast.error("Please provide open and close times for all days");
      return;
    }
    updateWorkingHours(workingHours, {
      onSuccess: (res) => {
        toast.success(res?.message || "Working hours updated successfully");
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to update working hours");
      },
    });
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Working Hours
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={2}>
          {daysOfWeek.map((day, index) => (
            <Box key={day} display="flex" gap={2} alignItems="center">
              <Typography sx={{ minWidth: 100, textTransform: "capitalize" }}>
                {day}
              </Typography>
              <TextField
                label="Open"
                type="time"
                value={workingHours[index]?.open || ""}
                onChange={(e) => handleChange(index, "open", e.target.value)}
                InputLabelProps={{ shrink: true }}
              />
              <TextField
                label="Close"
                type="time"
                value={workingHours[index]?.close || ""}
                onChange={(e) => handleChange(index, "close", e.target.value)}
                InputLabelProps={{ shrink: true }}
              />
            </Box>
          ))}

          <Button
            type="submit"
            variant="contained"
            color="primary"
            disabled={isLoading}
          >
            {isLoading ? "Updating..." : "Update Working Hours"}
          </Button>
        </CustomStackFullWidth>
      </Box>
    </CustomStackFullWidth>
  );
};

export default WorkingHoursForm;

