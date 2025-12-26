import React from "react";
import { Typography, Box, Card, CardContent, CircularProgress, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetVendorProfile } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorProfile";
import { getVendorToken } from "helper-functions/getToken";

const ProfileView = () => {
  const vendorToken = getVendorToken();
  const { data, isLoading } = useGetVendorProfile(!!vendorToken);

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  const profile = data?.data || {};

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Vendor Profile
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={2}>
            <Box>
              <Typography variant="subtitle2" fontWeight="bold" gutterBottom>
                Business Information
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Business Type: {profile.business_type || "N/A"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                License Number: {profile.license_number || "N/A"}
              </Typography>
              {profile.license_expiry && (
                <Typography variant="body2" color="text.secondary">
                  License Expiry: {profile.license_expiry}
                </Typography>
              )}
            </Box>

            {profile.is_verified !== undefined && (
              <Box>
                <Chip
                  label={profile.is_verified ? "Verified" : "Not Verified"}
                  color={profile.is_verified ? "success" : "default"}
                />
              </Box>
            )}
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default ProfileView;

