import React, { useState, useEffect } from "react";
import { Box, TextField, Button, Typography, Chip, IconButton } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { toast } from "react-hot-toast";
import { useManageHolidays } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useManageHolidays";
import { useGetVendorProfile } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorProfile";
import DeleteIcon from "@mui/icons-material/Delete";

const HolidaysManager = () => {
  const { data: profileData, refetch } = useGetVendorProfile();
  const { mutate: manageHolidays, isLoading } = useManageHolidays();
  const [newHoliday, setNewHoliday] = useState("");

  const holidays = profileData?.data?.holidays || [];

  const handleAdd = () => {
    if (!newHoliday) {
      toast.error("Please select a date");
      return;
    }
    const updatedHolidays = Array.from(new Set([...holidays, newHoliday]));
    manageHolidays(
      {
        action: "replace",
        holidays: updatedHolidays,
      },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Holiday added successfully");
          setNewHoliday("");
          refetch();
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to add holiday");
        },
      }
    );
  };

  const handleRemove = (holiday) => {
    const updatedHolidays = holidays.filter((h) => h !== holiday);
    manageHolidays(
      {
        action: "replace",
        holidays: updatedHolidays,
      },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Holiday removed successfully");
          refetch();
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to remove holiday");
        },
      }
    );
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Holidays Management
      </Typography>

      <Box display="flex" gap={2} alignItems="center">
        <TextField
          label="Add Holiday"
          type="date"
          value={newHoliday}
          onChange={(e) => setNewHoliday(e.target.value)}
          InputLabelProps={{ shrink: true }}
        />
        <Button
          variant="contained"
          color="primary"
          onClick={handleAdd}
          disabled={isLoading}
        >
          Add
        </Button>
      </Box>

      <Box display="flex" flexWrap="wrap" gap={1}>
        {holidays.map((holiday, index) => (
          <Chip
            key={index}
            label={holiday}
            onDelete={() => handleRemove(holiday)}
            deleteIcon={<DeleteIcon />}
          />
        ))}
      </Box>
    </CustomStackFullWidth>
  );
};

export default HolidaysManager;

