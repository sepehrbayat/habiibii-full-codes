import React, { useMemo, useState } from "react";
import { Typography, Box, Card, CardContent, TextField, Button, Grid, CircularProgress, FormControl, InputLabel, Select, MenuItem, Pagination } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetLoyaltyPoints from "../../../../../api-manage/hooks/react-query/beauty/useGetLoyaltyPoints";
import useGetLoyaltyCampaigns from "../../../../../api-manage/hooks/react-query/beauty/useGetLoyaltyCampaigns";
import { useRedeemLoyaltyPoints } from "../../../../../api-manage/hooks/react-query/beauty/useRedeemLoyaltyPoints";
import { getToken } from "helper-functions/getToken";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";

const LoyaltyPoints = () => {
  const token = getToken();
  const [selectedCampaign, setSelectedCampaign] = useState("");
  const [points, setPoints] = useState("");
  const [salonId, setSalonId] = useState("");
  const [page, setPage] = useState(1);
  const limit = 6;
  const offset = (page - 1) * limit;

  const { data: pointsData, isLoading: pointsLoading } = useGetLoyaltyPoints(!!token);
  const { data: campaignsData, isLoading: campaignsLoading } = useGetLoyaltyCampaigns(
    { salon_id: salonId || undefined, limit, offset },
    !!token
  );
  const { mutate: redeemPoints, isLoading: isRedeeming } = useRedeemLoyaltyPoints();

  const pointsInfo = pointsData?.data || {};
  const campaigns = campaignsData?.data || [];
  const totalPages = useMemo(() => {
    if (!campaignsData?.total) return 1;
    return Math.max(1, Math.ceil(campaignsData.total / limit));
  }, [campaignsData?.total, limit]);

  const handleRedeem = () => {
    if (!selectedCampaign || !points) {
      toast.error("Please select a campaign and enter points");
      return;
    }

    redeemPoints(
      {
        campaign_id: selectedCampaign,
        points: parseInt(points),
      },
      {
        onSuccess: (response) => {
          const reward = response?.data?.reward;
          if (reward) {
            switch (reward.type) {
              case "discount_percentage":
                toast.success(
                  `${reward.value}% discount: ${reward.description}`,
                  { duration: 5000 }
                );
                break;
              case "discount_amount":
                toast.success(
                  `${reward.value} discount: ${reward.description}`,
                  { duration: 5000 }
                );
                break;
              case "wallet_credit":
                toast.success(
                  `${reward.value} added to wallet. New balance: ${reward.wallet_balance}`,
                  { duration: 5000 }
                );
                break;
              case "cashback":
                toast.success(
                  `${reward.value} cashback added. New balance: ${reward.wallet_balance}`,
                  { duration: 5000 }
                );
                break;
              case "gift_card":
                toast.success(
                  `Gift card created: ${reward.gift_card_code}. Amount: ${reward.value}`,
                  { duration: 6000 }
                );
                // You can show a modal here with gift card details if needed
                if (reward.gift_card_id && reward.expires_at) {
                  console.log("Gift Card Details:", {
                    id: reward.gift_card_id,
                    code: reward.gift_card_code,
                    amount: reward.value,
                    expires_at: reward.expires_at,
                  });
                }
                break;
              case "points_redeemed":
                toast.success(
                  `${reward.points} points redeemed: ${reward.description}`,
                  { duration: 5000 }
                );
                break;
              default:
                toast.success("Points redeemed successfully");
            }
          } else {
            toast.success("Points redeemed successfully");
          }
          setSelectedCampaign("");
          setPoints("");
          // Refetch points data to update the display
          if (pointsData) {
            // The query will automatically refetch if needed
          }
        },
        onError: (error) => {
          toast.error(getBeautyErrorMessage(error) || "Failed to redeem points");
        },
      }
    );
  };

  if (!token) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view your loyalty points</Typography>
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Loyalty Points
      </Typography>

      {pointsLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : (
        <Grid container spacing={3}>
          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <CustomStackFullWidth spacing={2}>
                  <Typography variant="h6" color="text.secondary">
                    Total Points
                  </Typography>
                  <Typography variant="h3" color="primary" fontWeight="bold">
                    {pointsInfo.total_points || 0}
                  </Typography>
                </CustomStackFullWidth>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <CustomStackFullWidth spacing={2}>
                  <Typography variant="h6" color="text.secondary">
                    Used Points
                  </Typography>
                  <Typography variant="h3" color="error" fontWeight="bold">
                    {pointsInfo.used_points || 0}
                  </Typography>
                </CustomStackFullWidth>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <CustomStackFullWidth spacing={2}>
                  <Typography variant="h6" color="text.secondary">
                    Available Points
                  </Typography>
                  <Typography variant="h3" color="success.main" fontWeight="bold">
                    {pointsInfo.available_points || 0}
                  </Typography>
                </CustomStackFullWidth>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      )}

      <Card>
        <CardContent>
          <CustomStackFullWidth spacing={3}>
            <Typography variant="h5" fontWeight="bold">
              Redeem Points
            </Typography>

            {campaignsLoading ? (
              <Box display="flex" justifyContent="center" p={2}>
                <CircularProgress />
              </Box>
            ) : campaigns.length > 0 ? (
              <>
                <FormControl fullWidth>
                  <InputLabel>Filter by Salon</InputLabel>
                  <Select
                    value={salonId}
                    label="Filter by Salon"
                    onChange={(e) => {
                      setSalonId(e.target.value);
                      setPage(1);
                    }}
                  >
                    <MenuItem value="">All Salons</MenuItem>
                    {Array.from(new Set(campaigns.map((campaign) => campaign.salon_id))).map(
                      (id) =>
                        id && (
                          <MenuItem key={id} value={id}>
                            Salon #{id}
                          </MenuItem>
                        )
                    )}
                  </Select>
                </FormControl>

                <TextField
                  fullWidth
                  select
                  label="Select Campaign"
                  value={selectedCampaign}
                  onChange={(e) => setSelectedCampaign(e.target.value)}
                  SelectProps={{
                    native: true,
                  }}
                >
                  <option value="">Select a campaign</option>
                  {campaigns.map((campaign) => (
                    <option key={campaign.id} value={campaign.id}>
                      {campaign.name}
                    </option>
                  ))}
                </TextField>

                <TextField
                  fullWidth
                  label="Points to Redeem"
                  type="number"
                  value={points}
                  onChange={(e) => setPoints(e.target.value)}
                />

                <Button
                  variant="contained"
                  color="primary"
                  size="large"
                  onClick={handleRedeem}
                  disabled={isRedeeming || !selectedCampaign || !points}
                  fullWidth
                >
                  {isRedeeming ? "Redeeming..." : "Redeem Points"}
                </Button>

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
              </>
            ) : (
              <Typography variant="body1" color="text.secondary">
                No active campaigns available
              </Typography>
            )}
          </CustomStackFullWidth>
        </CardContent>
      </Card>
    </CustomStackFullWidth>
  );
};

export default LoyaltyPoints;

