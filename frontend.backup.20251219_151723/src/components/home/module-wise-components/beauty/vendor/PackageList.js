import React from "react";
import { Typography, Box, CircularProgress, Card, CardContent } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetVendorPackages } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorPackages";
import { getVendorToken } from "helper-functions/getToken";

const PackageList = () => {
  const vendorToken = getVendorToken();

  const { data, isLoading } = useGetVendorPackages(
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

  const packages = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Packages
      </Typography>

      {packages.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {packages.map((pkg) => (
            <Card key={pkg.id}>
              <CardContent>
                <Typography variant="h6">{pkg.name || "N/A"}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Sessions: {pkg.sessions_count || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Price: ${pkg.total_price || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Validity: {pkg.validity_days || 0} days
                </Typography>
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No packages found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default PackageList;

