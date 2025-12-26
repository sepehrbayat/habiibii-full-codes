import React from "react";
import { Typography, Box, Button, Card, CardContent, Divider, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useQuery } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import { usePurchasePackage } from "../../../../../api-manage/hooks/react-query/beauty/usePurchasePackage";
import useGetPackageStatus from "../../../../../api-manage/hooks/react-query/beauty/useGetPackageStatus";
import useGetPackageUsageHistory from "../../../../../api-manage/hooks/react-query/beauty/useGetPackageUsageHistory";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { CircularProgress } from "@mui/material";
import { useRouter } from "next/router";

const PackageDetails = ({ packageId }) => {
  const router = useRouter();
  const { data, isLoading } = useQuery(
    ["beauty-package-details", packageId],
    () => BeautyApi.getPackageDetails(packageId),
    { enabled: !!packageId }
  );
  const { data: packageStatusData, isLoading: statusLoading } = useGetPackageStatus(packageId, {
    enabled: !!packageId,
  });
  const { data: usageHistoryData, isLoading: usageLoading } = useGetPackageUsageHistory(packageId, !!packageId);
  const { mutate: purchasePackage, isLoading: isPurchasing } = usePurchasePackage();

  const packageData = data?.data || data;
  const packageStatus = packageStatusData?.data;
  const usageHistory = usageHistoryData?.data || usageHistoryData || [];

  const handlePurchase = (paymentMethod) => {
    // Convert 'online' to 'digital_payment' for Laravel compatibility
    const convertedPaymentMethod = paymentMethod === 'online' 
      ? 'digital_payment' 
      : paymentMethod;
    
    purchasePackage(
      { id: packageId, paymentMethod: convertedPaymentMethod },
      {
        onSuccess: (response) => {
          toast.success("Package purchased successfully");
          if (response?.data?.redirect_url) {
            window.location.href = response.data.redirect_url;
          } else {
            router.push("/beauty/packages");
          }
        },
        onError: (error) => {
          toast.error(getBeautyErrorMessage(error) || "Failed to purchase package");
        },
      }
    );
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (!packageData) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Package not found</Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        {packageData.name}
      </Typography>

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={3}>
            {packageData.description && (
              <Box>
                <Typography variant="body1">{packageData.description}</Typography>
              </Box>
            )}

            <Divider />

            <Box>
              <Typography variant="h5" color="primary" fontWeight="bold">
                ${packageData.total_price || 0}
              </Typography>
              <Typography variant="body1">
                {packageData.sessions_count} sessions included
              </Typography>
              {packageData.validity_days && (
                <Typography variant="body2" color="text.secondary">
                  Valid for {packageData.validity_days} days
                </Typography>
              )}
            </Box>

            {packageStatus && (
              <>
                <Divider />
                <Box>
                  <Typography variant="h6" fontWeight="bold" gutterBottom>
                    Package Status
                  </Typography>
                  <Box display="flex" flexDirection="column" gap={1} mt={2}>
                    <Box display="flex" justifyContent="space-between" alignItems="center">
                      <Typography variant="body1">Total Sessions:</Typography>
                      <Typography variant="body1" fontWeight="bold">
                        {packageStatus.total_sessions}
                      </Typography>
                    </Box>
                    <Box display="flex" justifyContent="space-between" alignItems="center">
                      <Typography variant="body1">Remaining Sessions:</Typography>
                      <Typography
                        variant="body1"
                        fontWeight="bold"
                        color={packageStatus.remaining_sessions > 0 ? "success.main" : "error"}
                      >
                        {packageStatus.remaining_sessions}
                      </Typography>
                    </Box>
                    <Box display="flex" justifyContent="space-between" alignItems="center">
                      <Typography variant="body1">Used Sessions:</Typography>
                      <Typography variant="body1" fontWeight="bold">
                        {packageStatus.used_sessions}
                      </Typography>
                    </Box>
                    <Box display="flex" justifyContent="space-between" alignItems="center">
                      <Typography variant="body1">Status:</Typography>
                      <Chip
                        label={packageStatus.is_valid ? "Valid" : "Expired"}
                        color={packageStatus.is_valid ? "success" : "error"}
                        size="small"
                      />
                    </Box>
                  </Box>
                </Box>
              </>
            )}

            {(usageLoading || (usageHistory && usageHistory.length)) && (
              <>
                <Divider />
                <Box>
                  <Typography variant="h6" fontWeight="bold" gutterBottom>
                    Usage History
                  </Typography>
                  {usageLoading ? (
                    <Box display="flex" justifyContent="center" p={2}>
                      <CircularProgress size={24} />
                    </Box>
                  ) : usageHistory.length > 0 ? (
                    <CustomStackFullWidth spacing={1}>
                      {usageHistory.map((usage, index) => (
                        <Box
                          key={usage.id || index}
                          sx={{
                            p: 1.5,
                            bgcolor: "background.default",
                            borderRadius: 1,
                            border: "1px solid",
                            borderColor: "divider",
                          }}
                        >
                          <Typography variant="body2" fontWeight="bold">
                            Session {usage.session_number || usage.session || index + 1}
                          </Typography>
                          <Typography variant="body2" color="text.secondary">
                            {usage.used_at || usage.created_at || "-"}
                          </Typography>
                          {usage.status && (
                            <Chip
                              label={usage.status}
                              size="small"
                              color={usage.status === "completed" ? "success" : "default"}
                              sx={{ mt: 0.5 }}
                            />
                          )}
                          {usage.staff && (
                            <Typography variant="caption" color="text.secondary" display="block">
                              Staff: {usage.staff.name || usage.staff_id}
                            </Typography>
                          )}
                        </Box>
                      ))}
                    </CustomStackFullWidth>
                  ) : (
                    <Typography variant="body2" color="text.secondary">
                      No usage recorded yet
                    </Typography>
                  )}
                </Box>
              </>
            )}

            <Box display="flex" flexDirection="column" gap={2}>
              <Button
                variant="contained"
                color="primary"
                size="large"
                onClick={() => handlePurchase("cash_payment")}
                disabled={isPurchasing}
                fullWidth
              >
                {isPurchasing ? "Processing..." : "Purchase with Cash"}
              </Button>
              <Button
                variant="outlined"
                color="primary"
                size="large"
                onClick={() => handlePurchase("wallet")}
                disabled={isPurchasing}
                fullWidth
              >
                Purchase with Wallet
              </Button>
              <Button
                variant="outlined"
                color="primary"
                size="large"
                onClick={() => handlePurchase("digital_payment")}
                disabled={isPurchasing}
                fullWidth
              >
                Purchase Online
              </Button>
            </Box>
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default PackageDetails;

