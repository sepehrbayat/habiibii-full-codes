import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getSubscriptionHistory = async (params) => {
  const { data } = await BeautyVendorApi.getSubscriptionHistory(params);
  return data;
};

export const useGetSubscriptionHistory = (params, enabled = true) => {
  return useQuery(
    ["vendor-subscription-history", params],
    () => getSubscriptionHistory(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

