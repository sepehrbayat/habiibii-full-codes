import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const purchaseSubscription = async (subscriptionData) => {
  const { data } = await BeautyVendorApi.purchaseSubscription(subscriptionData);
  return data;
};

const usePurchaseSubscription = () => {
  return useMutation("vendor-purchase-subscription", purchaseSubscription);
};

export { usePurchaseSubscription };

