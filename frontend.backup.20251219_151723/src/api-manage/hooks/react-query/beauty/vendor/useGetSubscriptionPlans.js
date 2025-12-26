import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getSubscriptionPlans = async () => {
  const { data } = await BeautyVendorApi.getSubscriptionPlans();
  return data;
};

export const useGetSubscriptionPlans = (enabled = true) => {
  return useQuery(
    ["vendor-subscription-plans"],
    () => getSubscriptionPlans(),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

