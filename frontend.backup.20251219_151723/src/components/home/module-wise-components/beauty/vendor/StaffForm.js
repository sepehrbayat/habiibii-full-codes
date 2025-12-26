import React, { useState, useEffect } from "react";
import {
  Box,
  TextField,
  Button,
  Typography,
  CircularProgress,
  FormControlLabel,
  Switch,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import { toast } from "react-hot-toast";
import { useCreateStaff } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCreateStaff";
import { useUpdateStaff } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useUpdateStaff";
import { useGetStaffDetails } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetStaffDetails";

const StaffForm = ({ staffId, onSuccess }) => {
  const router = useRouter();
  const isEdit = !!staffId;
  const { data: staffData, isLoading: isLoadingDetails } = useGetStaffDetails(staffId, isEdit);
  const { mutate: createStaff, isLoading: isCreating } = useCreateStaff();
  const { mutate: updateStaff, isLoading: isUpdating } = useUpdateStaff();

  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    avatar: null,
    specializations: [],
    working_hours: [],
    breaks: [],
    days_off: [],
    status: 1,
  });

  useEffect(() => {
    if (isEdit && staffData?.data) {
      const staff = staffData.data;
      setFormData({
        name: staff.name || "",
        email: staff.email || "",
        phone: staff.phone || "",
        avatar: null,
        specializations: staff.specializations || [],
        working_hours: staff.working_hours || [],
        breaks: staff.breaks || [],
        days_off: staff.days_off || [],
        status: staff.status !== undefined ? staff.status : 1,
      });
    }
  }, [isEdit, staffData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e) => {
    setFormData((prev) => ({ ...prev, avatar: e.target.files[0] }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (isEdit) {
      updateStaff(
        { id: staffId, staffData: formData },
        {
          onSuccess: (res) => {
            toast.success(res?.message || "Staff updated successfully");
            if (onSuccess) onSuccess();
            else router.push("/beauty/vendor/staff");
          },
          onError: (error) => {
            toast.error(error?.response?.data?.message || "Failed to update staff");
          },
        }
      );
    } else {
      createStaff(formData, {
        onSuccess: (res) => {
          toast.success(res?.message || "Staff created successfully");
          if (onSuccess) onSuccess();
          else router.push("/beauty/vendor/staff");
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to create staff");
        },
      });
    }
  };

  if (isEdit && isLoadingDetails) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        {isEdit ? "Edit Staff" : "Add New Staff"}
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={3}>
          <TextField
            label="Name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Email"
            name="email"
            type="email"
            value={formData.email}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Phone"
            name="phone"
            value={formData.phone}
            onChange={handleChange}
            required
            fullWidth
          />

          <Box>
            <Typography variant="body2" gutterBottom>
              Avatar
            </Typography>
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
            />
          </Box>

          <FormControlLabel
            control={
              <Switch
                checked={formData.status === 1}
                onChange={(e) =>
                  setFormData((prev) => ({
                    ...prev,
                    status: e.target.checked ? 1 : 0,
                  }))
                }
              />
            }
            label="Active"
          />

          <Box display="flex" gap={2}>
            <Button
              type="submit"
              variant="contained"
              color="primary"
              disabled={isCreating || isUpdating}
            >
              {isCreating || isUpdating
                ? "Saving..."
                : isEdit
                ? "Update Staff"
                : "Create Staff"}
            </Button>
            <Button
              variant="outlined"
              onClick={() => router.back()}
            >
              Cancel
            </Button>
          </Box>
        </CustomStackFullWidth>
      </Box>
    </CustomStackFullWidth>
  );
};

export default StaffForm;

