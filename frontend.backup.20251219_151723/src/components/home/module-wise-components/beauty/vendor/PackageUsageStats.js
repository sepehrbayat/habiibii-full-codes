import React, { useState } from "react";
import { Typography, Box, CircularProgress, Card, CardContent, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetPackageUsageStats } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetPackageUsageStats";
import { getVendorToken } from "helper-functions/getToken";
import PackageUsageChart from "./PackageUsageChart";

const PackageUsageStats = () => {
  const vendorToken = getVendorToken();
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [filters, setFilters] = useState({});

  const { data, isLoading } = useGetPackageUsageStats(
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

  const stats = data?.data || {};

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Package Usage Statistics
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
              Total Usage
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              {stats.total_usage || 0}
            </Typography>
          </CardContent>
        </Card>
        <Card>
          <CardContent>
            <Typography variant="body2" color="text.secondary">
              Active Packages
            </Typography>
            <Typography variant="h5" fontWeight="bold">
              {stats.active_packages || 0}
            </Typography>
          </CardContent>
        </Card>
      </Box>

      <PackageUsageChart data={stats} />
    </CustomStackFullWidth>
  );
};

export default PackageUsageStats;

