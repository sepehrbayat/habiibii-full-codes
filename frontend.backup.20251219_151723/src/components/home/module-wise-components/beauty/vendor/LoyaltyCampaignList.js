import React from "react";
import { Typography, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import CampaignCard from "./CampaignCard";
import { useGetVendorLoyaltyCampaigns } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorLoyaltyCampaigns";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";

const LoyaltyCampaignList = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();

  const { data, isLoading } = useGetVendorLoyaltyCampaigns(
    {
      limit: 25,
      offset: 0,
    },
    !!vendorToken
  );

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  const campaigns = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Loyalty Campaigns
      </Typography>

      {campaigns.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {campaigns.map((campaign) => (
            <CampaignCard
              key={campaign.id}
              campaign={campaign}
              onViewStats={(id) => router.push(`/beauty/vendor/loyalty/campaigns/${id}/stats`)}
            />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No campaigns found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default LoyaltyCampaignList;

