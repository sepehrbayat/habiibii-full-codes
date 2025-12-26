import React from "react";
import { Card, CardContent, Typography, Box, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const CampaignCard = ({ campaign, onViewStats }) => {
  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {campaign.name || "Campaign"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {campaign.description || "No description"}
              </Typography>
            </Box>
          </Box>

          <Box>
            <Typography variant="body2" color="text.secondary">
              Points: {campaign.points || 0}
            </Typography>
            {campaign.start_date && (
              <Typography variant="body2" color="text.secondary">
                Start: {campaign.start_date}
              </Typography>
            )}
            {campaign.end_date && (
              <Typography variant="body2" color="text.secondary">
                End: {campaign.end_date}
              </Typography>
            )}
          </Box>

          {onViewStats && (
            <Button
              variant="outlined"
              color="primary"
              onClick={() => onViewStats(campaign.id)}
              fullWidth
            >
              View Statistics
            </Button>
          )}
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default CampaignCard;

