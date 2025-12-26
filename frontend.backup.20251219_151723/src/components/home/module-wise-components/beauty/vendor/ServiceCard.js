import React from "react";
import { Card, CardContent, Typography, Box, Chip, Button, IconButton } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";
import EditIcon from "@mui/icons-material/Edit";
import { useToggleServiceStatus } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useToggleServiceStatus";
import { toast } from "react-hot-toast";

const ServiceCard = ({ service, onViewDetails, onRefetch }) => {
  const router = useRouter();
  const { mutate: toggleStatus, isLoading } = useToggleServiceStatus();

  const handleToggleStatus = (e) => {
    e.stopPropagation();
    toggleStatus(service.id, {
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
      onViewDetails(service.id);
    } else {
      router.push(`/beauty/vendor/services/${service.id}`);
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {service.name || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {service.description || "No description"}
              </Typography>
            </Box>
            <Box display="flex" alignItems="center" gap={1}>
              <Chip
                label={service.status === 1 ? "Active" : "Inactive"}
                color={service.status === 1 ? "success" : "default"}
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

          <Box>
            <Typography variant="body2" color="text.secondary">
              Price: ${service.price || 0}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Duration: {service.duration_minutes || 0} minutes
            </Typography>
          </Box>

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

export default ServiceCard;

