import React, { useState } from "react";
import { Typography, Box, CircularProgress, Card, CardContent, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetPointsHistory } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetPointsHistory";
import { getVendorToken } from "helper-functions/getToken";

const PointsHistory = () => {
  const vendorToken = getVendorToken();
  const [userId, setUserId] = useState("");

  const { data, isLoading } = useGetPointsHistory(
    {
      limit: 25,
      offset: 0,
      user_id: userId || undefined,
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
        Points History
      </Typography>

      <Box>
        <TextField
          label="Filter by User ID"
          type="number"
          value={userId}
          onChange={(e) => setUserId(e.target.value)}
          sx={{ mb: 2 }}
        />
      </Box>

      {history.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {history.map((item) => (
            <Card key={item.id}>
              <CardContent>
                <Typography variant="h6">User: {item.user?.name || item.user_id || "N/A"}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Points: {item.points || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Type: {item.type || "N/A"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Date: {item.created_at || "N/A"}
                </Typography>
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No points history
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default PointsHistory;

