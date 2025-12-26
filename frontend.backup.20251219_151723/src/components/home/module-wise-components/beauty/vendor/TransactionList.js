import React, { useState } from "react";
import { Typography, Box, CircularProgress, Card, CardContent, TextField, Button, Select, MenuItem, FormControl, InputLabel } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetTransactionHistory } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetTransactionHistory";
import { getVendorToken } from "helper-functions/getToken";

const TransactionList = () => {
  const vendorToken = getVendorToken();
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [transactionType, setTransactionType] = useState("");
  const [filters, setFilters] = useState({});

  const { data, isLoading } = useGetTransactionHistory(
    {
      limit: 25,
      offset: 0,
      transaction_type: filters.transaction_type,
      date_from: filters.date_from,
      date_to: filters.date_to,
    },
    !!vendorToken
  );

  const handleApplyFilters = () => {
    setFilters({
      transaction_type: transactionType || undefined,
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

  const transactions = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3}>
      <Typography variant="h4" fontWeight="bold">
        Transaction History
      </Typography>

      <Box display="flex" gap={2} flexWrap="wrap">
        <FormControl sx={{ minWidth: 200 }}>
          <InputLabel>Transaction Type</InputLabel>
          <Select
            value={transactionType}
            label="Transaction Type"
            onChange={(e) => setTransactionType(e.target.value)}
          >
            <MenuItem value="">All</MenuItem>
            <MenuItem value="earning">Earning</MenuItem>
            <MenuItem value="payout">Payout</MenuItem>
            <MenuItem value="commission">Commission</MenuItem>
          </Select>
        </FormControl>
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

      {transactions.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {transactions.map((transaction) => (
            <Card key={transaction.id}>
              <CardContent>
                <Typography variant="h6">{transaction.transaction_type || "N/A"}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Amount: ${transaction.amount || 0}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Date: {transaction.created_at || "N/A"}
                </Typography>
                {transaction.description && (
                  <Typography variant="body2" color="text.secondary">
                    {transaction.description}
                  </Typography>
                )}
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No transactions found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default TransactionList;

