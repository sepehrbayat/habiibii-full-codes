import React, { useMemo, useState } from "react";
import {
  Box,
  Card,
  CardContent,
  CircularProgress,
  Pagination,
  Typography,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useQuery } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import { data_limit } from "../../../../../api-manage/ApiRoutes";

const WalletTransactions = () => {
  const [page, setPage] = useState(1);
  const offset = (page - 1) * data_limit;

  const { data, isLoading, isFetching } = useQuery(
    ["beauty-wallet-transactions", offset],
    async () => {
      const response = await BeautyApi.getWalletTransactions({
        offset,
        limit: data_limit,
        transaction_type: "beauty",
      });
      return response.data;
    }
  );

  const transactions = useMemo(() => {
    return data?.data || data?.transactions || [];
  }, [data]);

  const total = data?.total || data?.total_size || transactions.length;
  const totalPages = Math.max(1, Math.ceil(total / data_limit));

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Wallet Transactions (Beauty)
      </Typography>

      {isLoading || isFetching ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : transactions.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {transactions.map((tx) => (
            <Card key={tx.id || `${tx.transaction_type}-${tx.created_at}`}>
              <CardContent>
                <Typography variant="h6" fontWeight="bold">
                  {tx.transaction_type || "N/A"}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Amount: {tx.debit ? `- ${tx.debit}` : tx.credit || 0}
                </Typography>
                {tx.description && (
                  <Typography variant="body2" color="text.secondary">
                    {tx.description}
                  </Typography>
                )}
                {tx.created_at && (
                  <Typography variant="caption" color="text.secondary">
                    {tx.created_at}
                  </Typography>
                )}
              </CardContent>
            </Card>
          ))}

          {totalPages > 1 && (
            <Box display="flex" justifyContent="center">
              <Pagination
                count={totalPages}
                page={page}
                onChange={(event, value) => setPage(value)}
                color="primary"
              />
            </Box>
          )}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No beauty wallet transactions found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default WalletTransactions;

