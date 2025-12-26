import React from "react";
import { Typography, Box, CircularProgress, Card, CardContent } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetSubscriptionHistory } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetSubscriptionHistory";
import { getVendorToken } from "helper-functions/getToken";

const SubscriptionHistory = () => {
  const vendorToken = getVendorToken();

  const { data, isLoading } = useGetSubscriptionHistory(
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

  const history = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Subscription History
      </Typography>

      {history.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {history.map((item) => (
            <Card key={item.id}>
              <CardContent>
                <Typography variant="h6">{item.plan_type || "N/A"}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Date: {item.created_at || "N/A"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Amount: ${item.amount || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Status: {item.status || "N/A"}
                </Typography>
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No subscription history
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default SubscriptionHistory;

