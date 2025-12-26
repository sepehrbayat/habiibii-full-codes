import React from "react";
import { Card, CardContent, Typography, Box, Button, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const SubscriptionCard = ({ plan, onSelect, isSelected }) => {
  return (
    <Card variant={isSelected ? "outlined" : "elevation"} sx={{ borderColor: isSelected ? "primary.main" : undefined }}>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="center">
            <Typography variant="h6" fontWeight="bold">
              {plan.name || "Plan"}
            </Typography>
            {isSelected && <Chip label="Selected" color="primary" size="small" />}
          </Box>
          <Typography variant="body2" color="text.secondary">
            {plan.description || "No description"}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Type: {plan.subscription_type}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Duration: {plan.duration_days} days
          </Typography>
          {plan.ad_position && (
            <Typography variant="body2" color="text.secondary">
              Position: {plan.ad_position}
            </Typography>
          )}
          <Typography variant="h5" color="primary.main" fontWeight="bold">
            ${plan.price || 0}
          </Typography>
          <Box>
            <Button variant={isSelected ? "contained" : "outlined"} onClick={onSelect} fullWidth>
              {isSelected ? "Selected" : "Select"}
            </Button>
          </Box>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default SubscriptionCard;

