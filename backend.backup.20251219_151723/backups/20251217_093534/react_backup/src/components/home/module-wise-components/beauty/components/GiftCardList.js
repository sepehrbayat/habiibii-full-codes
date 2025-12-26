import React, { useMemo, useState } from "react";
import { Typography, Box, Card, CardContent, TextField, Button, CircularProgress, Pagination } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetGiftCards from "../../../../../api-manage/hooks/react-query/beauty/useGetGiftCards";
import { useRedeemGiftCard } from "../../../../../api-manage/hooks/react-query/beauty/useRedeemGiftCard";
import { getToken } from "helper-functions/getToken";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import { useRouter } from "next/router";

const GiftCardList = () => {
  const token = getToken();
  const router = useRouter();
  const [redeemCode, setRedeemCode] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;
  const offset = (page - 1) * limit;
  const { data, isLoading, refetch } = useGetGiftCards({ limit, offset }, !!token);
  const { mutate: redeemGiftCard, isLoading: isRedeeming } = useRedeemGiftCard();

  const giftCards = data?.data || [];
  const totalPages = useMemo(() => {
    if (!data?.total) return 1;
    return Math.max(1, Math.ceil(data.total / limit));
  }, [data?.total, limit]);

  const handleRedeem = () => {
    if (!redeemCode.trim()) {
      toast.error("Please enter a gift card code");
      return;
    }

    redeemGiftCard(redeemCode, {
      onSuccess: () => {
        toast.success("Gift card redeemed successfully");
        setRedeemCode("");
        refetch();
      },
      onError: (error) => {
        toast.error(getBeautyErrorMessage(error) || "Failed to redeem gift card");
      },
    });
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Gift Cards
      </Typography>

      <Box display="flex" justifyContent="flex-end">
        <Button variant="contained" onClick={() => router.push("/beauty/gift-cards/purchase")}>
          Purchase Gift Card
        </Button>
      </Box>

      {token && (
        <Card>
          <CardContent>
            <CustomStackFullWidth spacing={2}>
              <Typography variant="h6">Redeem Gift Card</Typography>
              <Box display="flex" gap={2}>
                <TextField
                  fullWidth
                  label="Gift Card Code"
                  value={redeemCode}
                  onChange={(e) => setRedeemCode(e.target.value)}
                  placeholder="Enter gift card code"
                />
                <Button
                  variant="contained"
                  color="primary"
                  onClick={handleRedeem}
                  disabled={isRedeeming}
                >
                  {isRedeeming ? "Redeeming..." : "Redeem"}
                </Button>
              </Box>
            </CustomStackFullWidth>
          </CardContent>
        </Card>
      )}

      {token && (
        <>
          <Typography variant="h5" fontWeight="bold">
            My Gift Cards
          </Typography>

          {isLoading ? (
            <Box display="flex" justifyContent="center" p={4}>
              <CircularProgress />
            </Box>
          ) : giftCards.length > 0 ? (
            <>
              <CustomStackFullWidth spacing={2}>
                {giftCards.map((card) => (
                  <Card key={card.id}>
                    <CardContent>
                      <CustomStackFullWidth spacing={1}>
                        <Typography variant="h6" fontWeight="bold">
                          Code: {card.code}
                        </Typography>
                        <Typography variant="body1" color="primary" fontWeight="bold">
                          ${card.amount}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Status: {card.status}
                        </Typography>
                        {card.expires_at && (
                          <Typography variant="body2" color="text.secondary">
                            Expires: {card.expires_at}
                          </Typography>
                        )}
                      </CustomStackFullWidth>
                    </CardContent>
                  </Card>
                ))}
              </CustomStackFullWidth>

              {totalPages > 1 && (
                <Box display="flex" justifyContent="center" mt={3}>
                  <Pagination
                    count={totalPages}
                    page={page}
                    onChange={(event, value) => setPage(value)}
                    color="primary"
                  />
                </Box>
              )}
            </>
          ) : (
            <Box p={4} textAlign="center">
              <Typography variant="body1" color="text.secondary">
                No gift cards found
              </Typography>
            </Box>
          )}
        </>
      )}

      {!token && (
        <Box p={4} textAlign="center">
          <Typography variant="h6">Please login to view your gift cards</Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default GiftCardList;

