import React from "react";
import { Card, CardContent, Typography, Box, Button, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const PackageCard = ({ package: pkg, onViewDetails }) => {
  return (
    <Card sx={{ height: "100%", display: "flex", flexDirection: "column" }}>
      <CardContent sx={{ flexGrow: 1 }}>
        <CustomStackFullWidth spacing={2}>
          <Typography variant="h6" fontWeight="bold">
            {pkg.name}
          </Typography>

          {pkg.description && (
            <Typography variant="body2" color="text.secondary">
              {pkg.description}
            </Typography>
          )}

          <Box>
            <Typography variant="h5" color="primary" fontWeight="bold">
              ${pkg.total_price || 0}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              {pkg.sessions_count} sessions
            </Typography>
            {pkg.validity_days && (
              <Typography variant="body2" color="text.secondary">
                Valid for {pkg.validity_days} days
              </Typography>
            )}
          </Box>

          {pkg.status !== undefined && (
            <Chip
              label={pkg.status === 1 ? "Active" : "Inactive"}
              color={pkg.status === 1 ? "success" : "default"}
              size="small"
            />
          )}

          <Button
            variant="contained"
            color="primary"
            onClick={() => onViewDetails(pkg.id)}
            fullWidth
          >
            View Details
          </Button>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default PackageCard;

