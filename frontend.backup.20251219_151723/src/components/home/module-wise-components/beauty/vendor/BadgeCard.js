import React from "react";
import { Card, CardContent, Typography, Box, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const BadgeCard = ({ badge }) => {
  const getBadgeColor = (badgeType) => {
    switch (badgeType) {
      case "top_rated":
        return "success";
      case "featured":
        return "primary";
      case "verified":
        return "info";
      default:
        return "default";
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="center">
            <Typography variant="h6" fontWeight="bold">
              {badge.name || badge.type || "Badge"}
            </Typography>
            <Chip
              label={badge.type || "N/A"}
              color={getBadgeColor(badge.type)}
              size="small"
            />
          </Box>
          {badge.description && (
            <Typography variant="body2" color="text.secondary">
              {badge.description}
            </Typography>
          )}
          {badge.expires_at && (
            <Typography variant="body2" color="text.secondary">
              Expires: {badge.expires_at}
            </Typography>
          )}
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default BadgeCard;

