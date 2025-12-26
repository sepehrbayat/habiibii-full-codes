import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getVendorLoyaltyCampaigns = async (params) => {
  const { data } = await BeautyVendorApi.listLoyaltyCampaigns(params);
  return data;
};

export const useGetVendorLoyaltyCampaigns = (params, enabled = true) => {
  return useQuery(
    ["vendor-loyalty-campaigns", params],
    () => getVendorLoyaltyCampaigns(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

