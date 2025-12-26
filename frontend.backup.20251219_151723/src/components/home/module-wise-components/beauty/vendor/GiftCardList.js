import React from "react";
import { Typography, Box, CircularProgress, Card, CardContent, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetVendorGiftCards } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorGiftCards";
import { getVendorToken } from "helper-functions/getToken";

const GiftCardList = () => {
  const vendorToken = getVendorToken();

  const { data, isLoading } = useGetVendorGiftCards(
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

  const giftCards = data?.data || [];

  const getStatusColor = (status) => {
    switch (status) {
      case "active":
        return "success";
      case "redeemed":
        return "info";
      case "expired":
        return "error";
      default:
        return "default";
    }
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Gift Cards
      </Typography>

      {giftCards.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {giftCards.map((card) => (
            <Card key={card.id}>
              <CardContent>
                <Box display="flex" justifyContent="space-between" alignItems="center">
                  <Box>
                    <Typography variant="h6">{card.code || "N/A"}</Typography>
                    <Typography variant="body2" color="text.secondary">
                      Amount: ${card.amount || 0}
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Purchased: {card.purchased_at || "N/A"}
                    </Typography>
                  </Box>
                  <Chip
                    label={card.status || "N/A"}
                    color={getStatusColor(card.status)}
                    size="small"
                  />
                </Box>
              </CardContent>
            </Card>
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No gift cards found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default GiftCardList;

