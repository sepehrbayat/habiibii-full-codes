import React from "react";
import { Typography, Box, CircularProgress, Card, CardContent } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetCampaignStats } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetCampaignStats";
import { useRouter } from "next/router";

const CampaignStats = ({ campaignId }) => {
  const router = useRouter();
  const id = campaignId || router.query.id;
  const { data, isLoading } = useGetCampaignStats(id);

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  const stats = data?.data || {};

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Campaign Statistics
      </Typography>

      <Box display="flex" gap={2} flexWrap="wrap">
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Total Redemptions
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              {stats.total_redemptions || 0}
            </Typography>
          </CardContent>
        </Card>
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Points Awarded
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              {stats.points_awarded || 0}
            </Typography>
          </CardContent>
        </Card>
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Active Users
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              {stats.active_users || 0}
            </Typography>
          </CardContent>
        </Card>
      </Box>
    </CustomStackFullWidth>
  );
};

export default CampaignStats;

