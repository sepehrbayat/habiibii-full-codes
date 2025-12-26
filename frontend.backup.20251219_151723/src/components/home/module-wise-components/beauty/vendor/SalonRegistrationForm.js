import React, { useState } from "react";
import { Box, TextField, Button, Typography, Select, MenuItem, FormControl, InputLabel } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { toast } from "react-hot-toast";
import { useRegisterSalon } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useRegisterSalon";

const daysOfWeek = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

const SalonRegistrationForm = ({ onSuccess }) => {
  const router = useRouter();
  const { mutate: registerSalon, isLoading } = useRegisterSalon();
  const [formData, setFormData] = useState({
    business_type: "salon",
    license_number: "",
    license_expiry: "",
  });
  const [workingHours, setWorkingHours] = useState(
    daysOfWeek.map((day) => ({ day, open: "", close: "" }))
  );

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleWorkingHourChange = (day, field, value) => {
    setWorkingHours((prev) =>
      prev.map((entry) =>
        entry.day === day ? { ...entry, [field]: value } : entry
      )
    );
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const hasEmptyHours = workingHours.some((entry) => !entry.open || !entry.close);
    if (hasEmptyHours) {
      toast.error("Please provide working hours for all days");
      return;
    }
    registerSalon(
      { ...formData, working_hours: workingHours },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Salon registered successfully");
          if (onSuccess) {
            onSuccess();
          } else {
            router.push("/beauty/vendor/profile");
          }
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to register salon");
        },
      }
    );
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Salon Registration
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={3}>
          <FormControl fullWidth>
            <InputLabel>Business Type</InputLabel>
            <Select
              name="business_type"
              value={formData.business_type}
              onChange={handleChange}
              required
            >
              <MenuItem value="salon">Salon</MenuItem>
              <MenuItem value="clinic">Clinic</MenuItem>
            </Select>
          </FormControl>

          <TextField
            label="License Number"
            name="license_number"
            value={formData.license_number}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="License Expiry"
            name="license_expiry"
            type="date"
            value={formData.license_expiry}
            onChange={handleChange}
            required
            fullWidth
            InputLabelProps={{ shrink: true }}
          />

          <CustomStackFullWidth spacing={2}>
            <Typography variant="h6">Working Hours</Typography>
            {workingHours.map((entry) => (
              <Box key={entry.day} display="flex" gap={2} alignItems="center">
                <Typography sx={{ minWidth: 100, textTransform: "capitalize" }}>
                  {entry.day}
                </Typography>
                <TextField
                  type="time"
                  label="Open"
                  value={entry.open}
                  onChange={(e) => handleWorkingHourChange(entry.day, "open", e.target.value)}
                  InputLabelProps={{ shrink: true }}
                  required
                />
                <TextField
                  type="time"
                  label="Close"
                  value={entry.close}
                  onChange={(e) => handleWorkingHourChange(entry.day, "close", e.target.value)}
                  InputLabelProps={{ shrink: true }}
                  required
                />
              </Box>
            ))}
          </CustomStackFullWidth>

          <Button
            type="submit"
            variant="contained"
            color="primary"
            disabled={isLoading}
            fullWidth
          >
            {isLoading ? "Registering..." : "Register Salon"}
          </Button>
        </CustomStackFullWidth>
      </Box>
    </CustomStackFullWidth>
  );
};

export default SalonRegistrationForm;

