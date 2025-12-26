import React, { useMemo, useState } from "react";
import { Typography, Box, CircularProgress, Button, Select, MenuItem, FormControl, InputLabel, Alert } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useGetSubscriptionPlans } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetSubscriptionPlans";
import { usePurchaseSubscription } from "../../../../../api-manage/hooks/react-query/beauty/vendor/usePurchaseSubscription";
import { getVendorToken } from "helper-functions/getToken";
import { toast } from "react-hot-toast";
import SubscriptionCard from "./SubscriptionCard";

const normalizePlans = (apiData) => {
  const normalized = [];
  const plans = apiData?.plans || {};

  const pushPlan = ({ name, subscription_type, duration_days, price, ad_position }) => {
    normalized.push({
      name,
      subscription_type,
      duration_days: Number(duration_days),
      price: price ?? 0,
      ad_position,
      description: ad_position ? `${name} - ${ad_position}` : name,
      id: `${subscription_type}-${duration_days}-${ad_position || "na"}`,
    });
  };

  if (plans.featured_listing) {
    Object.entries(plans.featured_listing).forEach(([days, plan]) => {
      pushPlan({
        name: `Featured Listing (${days} days)`,
        subscription_type: "featured_listing",
        duration_days: days,
        price: plan?.price,
      });
    });
  }

  if (plans.boost_ads) {
    Object.entries(plans.boost_ads).forEach(([days, plan]) => {
      pushPlan({
        name: `Boost Ads (${days} days)`,
        subscription_type: "boost_ads",
        duration_days: days,
        price: plan?.price,
      });
    });
  }

  if (plans.banner_ads) {
    Object.entries(plans.banner_ads).forEach(([position, plan]) => {
      pushPlan({
        name: `Banner Ads (${position})`,
        subscription_type: "banner_ads",
        duration_days: 30,
        price: plan?.price,
        ad_position: position,
      });
    });
  }

  if (plans.dashboard_subscription) {
    const durationMap = { monthly: 30, yearly: 365 };
    Object.entries(plans.dashboard_subscription).forEach(([cycle, plan]) => {
      pushPlan({
        name: `Dashboard Subscription (${cycle})`,
        subscription_type: "dashboard_subscription",
        duration_days: durationMap[cycle] || 30,
        price: plan?.price,
      });
    });
  }

  return {
    plans: normalized,
    activeSubscriptions: apiData?.active_subscriptions || [],
  };
};

const SubscriptionPlans = () => {
  const vendorToken = getVendorToken();
  const { data, isLoading } = useGetSubscriptionPlans(!!vendorToken);
  const { mutate: purchaseSubscription, isLoading: isPurchasing } = usePurchaseSubscription();
  const [selectedPlanId, setSelectedPlanId] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("");

  const { plans, activeSubscriptions } = useMemo(() => normalizePlans(data?.data), [data]);

  const selectedPlan = plans.find((plan) => plan.id === selectedPlanId);

  const handlePurchase = () => {
    if (!selectedPlan || !paymentMethod) {
      toast.error("Please select a plan and payment method");
      return;
    }
    purchaseSubscription(
      {
        subscription_type: selectedPlan.subscription_type,
        duration_days: selectedPlan.duration_days,
        payment_method: paymentMethod,
        ad_position: selectedPlan.ad_position,
      },
      {
        onSuccess: (res) => {
          toast.success(res?.message || "Subscription purchased successfully");
        },
        onError: (error) => {
          toast.error(error?.response?.data?.message || "Failed to purchase subscription");
        },
      }
    );
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={4}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Subscription Plans
      </Typography>

      {activeSubscriptions?.length > 0 && (
        <Alert severity="info">
          Active Subscriptions:{" "}
          {activeSubscriptions.map((sub) => `${sub.subscription_type} (${sub.duration_days}d)`).join(", ")}
        </Alert>
      )}

      <CustomStackFullWidth spacing={2}>
        {plans.map((plan) => (
          <SubscriptionCard
            key={plan.id}
            plan={plan}
            onSelect={() => setSelectedPlanId(plan.id)}
            isSelected={selectedPlanId === plan.id}
          />
        ))}
      </CustomStackFullWidth>

      <Box>
        <FormControl fullWidth sx={{ mb: 2 }}>
          <InputLabel>Payment Method</InputLabel>
          <Select
            value={paymentMethod}
            label="Payment Method"
            onChange={(e) => setPaymentMethod(e.target.value)}
          >
            <MenuItem value="wallet">Wallet</MenuItem>
            <MenuItem value="digital_payment">Digital Payment</MenuItem>
            <MenuItem value="cash_payment">Cash Payment</MenuItem>
          </Select>
        </FormControl>

        <Button
          variant="contained"
          color="primary"
          onClick={handlePurchase}
          disabled={isPurchasing || !selectedPlan || !paymentMethod}
          fullWidth
        >
          {isPurchasing ? "Processing..." : "Purchase Subscription"}
        </Button>
      </Box>
    </CustomStackFullWidth>
  );
};

export default SubscriptionPlans;

