import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getRedemptionHistory = async (params) => {
  const { data } = await BeautyVendorApi.getRedemptionHistory(params);
  return data;
};

export const useGetRedemptionHistory = (params, enabled = true) => {
  return useQuery(
    ["vendor-redemption-history", params],
    () => getRedemptionHistory(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

