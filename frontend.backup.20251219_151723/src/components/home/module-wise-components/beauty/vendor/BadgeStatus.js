import React from "react";
import { Typography, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetBadgeStatus } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetBadgeStatus";
import { getVendorToken } from "helper-functions/getToken";
import BadgeCard from "./BadgeCard";

const BadgeStatus = () => {
  const vendorToken = getVendorToken();
  const { data, isLoading } = useGetBadgeStatus(!!vendorToken);

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  const badges = data?.data?.badges || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Badge Status
      </Typography>

      {badges.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {badges.map((badge, index) => (
            <BadgeCard key={index} badge={badge} />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No badges available
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default BadgeStatus;

