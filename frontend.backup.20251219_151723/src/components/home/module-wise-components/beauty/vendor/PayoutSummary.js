import React, { useState } from "react";
import { Typography, Box, CircularProgress, Card, CardContent, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetPayoutSummary } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetPayoutSummary";
import { getVendorToken } from "helper-functions/getToken";

const PayoutSummary = () => {
  const vendorToken = getVendorToken();
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [filters, setFilters] = useState({});

  const { data, isLoading, refetch } = useGetPayoutSummary(
    {
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

  const summary = data?.data || {};

  return (
    <CustomStackFullWidth spacing={3}>
      <Typography variant="h4" fontWeight="bold">
        Payout Summary
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

      <Box display="flex" gap={2} flexWrap="wrap">
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Total Earnings
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              ${summary.total_earnings || 0}
            </Typography>
          </CardContent>
        </Card>
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Commission
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              ${summary.commission || 0}
            </Typography>
          </CardContent>
        </Card>
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Net Payout
            </Typography>
            <Typography variant="h5" fontWeight="bold" color="success.main">
              ${summary.net_payout || 0}
            </Typography>
          </CardContent>
        </Card>
      </Box>
    </CustomStackFullWidth>
  );
};

export default PayoutSummary;

