import React, { useState } from "react";
import { Typography, Box, CircularProgress, Card, CardContent, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetRedemptionHistory } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetRedemptionHistory";
import { getVendorToken } from "helper-functions/getToken";

const RedemptionHistory = () => {
  const vendorToken = getVendorToken();
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [filters, setFilters] = useState({});

  const { data, isLoading } = useGetRedemptionHistory(
    {
      limit: 25,
      offset: 0,
      date_from: filters.date_from,
      date_to: filters.date_to,
    },
    !!vendorToken
  );

  const handleApplyFilters = () => {
    setFilters({
      date_from: dateFrom || undefined,
      date_to: dateTo || undefined,
    });
  };

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
        Redemption History
      </Typography>

      <Box display="flex" gap={2} flexWrap="wrap">
        <TextField
          label="Date From"
          type="date"
          value={dateFrom}
          onChange={(e) => setDateFrom(e.target.value)}
          InputLabelProps={{ shrink: true }}
        />
        <TextField
          label="Date To"
          type="date"
          value={dateTo}
          onChange={(e) => setDateTo(e.target.value)}
          InputLabelProps={{ shrink: true }}
        />
        <Button variant="contained" onClick={handleApplyFilters}>
          Apply Filters
        </Button>
      </Box>

      {history.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {history.map((item) => (
            <Card key={item.id}>
              <CardContent>
                <Typography variant="h6">Gift Card: {item.gift_card_code || "N/A"}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Amount: ${item.amount || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Redeemed: {item.redeemed_at || "N/A"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Customer: {item.user?.name || item.user?.email || "N/A"}
                </Typography>
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No redemption history
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default RedemptionHistory;

