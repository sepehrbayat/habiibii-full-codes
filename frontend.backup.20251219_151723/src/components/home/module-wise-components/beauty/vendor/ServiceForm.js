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
import { useCreateService } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useCreateService";
import { useUpdateService } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useUpdateService";
import { useGetServiceDetails } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetServiceDetails";
import { useGetVendorStaff } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorStaff";
import ServiceStaffAssignment from "./ServiceStaffAssignment";

const ServiceForm = ({ serviceId, onSuccess }) => {
  const router = useRouter();
  const isEdit = !!serviceId;
  const { data: serviceData, isLoading: isLoadingDetails } = useGetServiceDetails(serviceId, isEdit);
  const { data: staffData } = useGetVendorStaff({ limit: 100, offset: 0 });
  const { mutate: createService, isLoading: isCreating } = useCreateService();
  const { mutate: updateService, isLoading: isUpdating } = useUpdateService();

  const [formData, setFormData] = useState({
    category_id: "",
    name: "",
    description: "",
    duration_minutes: "",
    price: "",
    image: null,
    staff_ids: [],
    status: 1,
  });

  useEffect(() => {
    if (isEdit && serviceData?.data) {
      const service = serviceData.data;
      setFormData({
        category_id: service.category_id || "",
        name: service.name || "",
        description: service.description || "",
        duration_minutes: service.duration_minutes || "",
        price: service.price || "",
        image: null,
        staff_ids: service.staff_ids || [],
        status: service.status !== undefined ? service.status : 1,
      });
    }
  }, [isEdit, serviceData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e) => {
    setFormData((prev) => ({ ...prev, image: e.target.files[0] }));
  };

  const handleStaffChange = (staffIds) => {
    setFormData((prev) => ({ ...prev, staff_ids: staffIds }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (isEdit) {
      updateService(
        { id: serviceId, serviceData: formData },
        {
          onSuccess: (res) => {
            toast.success(res?.message || "Service updated successfully");
            if (onSuccess) onSuccess();
            else router.push("/beauty/vendor/services");
          },
          onError: (error) => {
            toast.error(error?.response?.data?.message || "Failed to update service");
          },
        }
      );
    } else {
      createService(formData, {
        onSuccess: (res) => {
          toast.success(res?.message || "Service created successfully");
          if (onSuccess) onSuccess();
          else router.push("/beauty/vendor/services");
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to create service");
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

  const staffList = staffData?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        {isEdit ? "Edit Service" : "Add New Service"}
      </Typography>

      <Box component="form" onSubmit={handleSubmit}>
        <CustomStackFullWidth spacing={3}>
          <TextField
            label="Category ID"
            name="category_id"
            type="number"
            value={formData.category_id}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Description"
            name="description"
            value={formData.description}
            onChange={handleChange}
            multiline
            rows={4}
            fullWidth
          />

          <TextField
            label="Duration (minutes)"
            name="duration_minutes"
            type="number"
            value={formData.duration_minutes}
            onChange={handleChange}
            required
            fullWidth
          />

          <TextField
            label="Price"
            name="price"
            type="number"
            value={formData.price}
            onChange={handleChange}
            required
            fullWidth
          />

          <Box>
            <Typography variant="body2" gutterBottom>
              Service Image
            </Typography>
            <input
              type="file"
              accept="image/*"
              onChange={handleFileChange}
            />
          </Box>

          <ServiceStaffAssignment
            staffList={staffList}
            selectedStaffIds={formData.staff_ids}
            onChange={handleStaffChange}
          />

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
                ? "Update Service"
                : "Create Service"}
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

export default ServiceForm;

