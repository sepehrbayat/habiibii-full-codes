import React from "react";
import { Card, CardContent, Typography, Box, Chip, Button, IconButton } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import EditIcon from "@mui/icons-material/Edit";
import { useToggleStaffStatus } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useToggleStaffStatus";
import { toast } from "react-hot-toast";

const StaffCard = ({ staff, onViewDetails, onRefetch }) => {
  const router = useRouter();
  const { mutate: toggleStatus, isLoading } = useToggleStaffStatus();

  const handleToggleStatus = (e) => {
    e.stopPropagation();
    toggleStatus(staff.id, {
      onSuccess: (res) => {
        toast.success(res?.message || "Status updated successfully");
        if (onRefetch) onRefetch();
      },
      onError: (error) => {
        toast.error(error?.response?.data?.message || "Failed to update status");
      },
    });
  };

  const handleViewDetails = () => {
    if (onViewDetails) {
      onViewDetails(staff.id);
    } else {
      router.push(`/beauty/vendor/staff/${staff.id}`);
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {staff.name || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Email: {staff.email || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Phone: {staff.phone || "N/A"}
              </Typography>
            </Box>
            <Box display="flex" alignItems="center" gap={1}>
              <Chip
                label={staff.status === 1 ? "Active" : "Inactive"}
                color={staff.status === 1 ? "success" : "default"}
                size="small"
              />
              <IconButton
                size="small"
                onClick={handleToggleStatus}
                disabled={isLoading}
              >
                <EditIcon />
              </IconButton>
            </Box>
          </Box>

          {staff.specializations && staff.specializations.length > 0 && (
            <Box>
              <Typography variant="body2" color="text.secondary">
                Specializations: {Array.isArray(staff.specializations) ? staff.specializations.join(", ") : staff.specializations}
              </Typography>
            </Box>
          )}

          <Button
            variant="outlined"
            color="primary"
            onClick={handleViewDetails}
            fullWidth
          >
            View Details
          </Button>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default StaffCard;

