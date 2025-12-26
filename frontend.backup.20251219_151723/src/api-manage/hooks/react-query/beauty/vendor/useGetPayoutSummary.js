import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getPayoutSummary = async (params) => {
  const { data } = await BeautyVendorApi.getPayoutSummary(params);
  return data;
};

export const useGetPayoutSummary = (params, enabled = true) => {
  return useQuery(
    ["vendor-payout-summary", params],
    () => getPayoutSummary(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

