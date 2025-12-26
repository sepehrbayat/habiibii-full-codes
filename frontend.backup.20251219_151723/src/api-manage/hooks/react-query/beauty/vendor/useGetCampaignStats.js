import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getCampaignStats = async (campaignId) => {
  const { data } = await BeautyVendorApi.getCampaignStats(campaignId);
  return data;
};

export const useGetCampaignStats = (campaignId, enabled = true) => {
  return useQuery(
    ["vendor-campaign-stats", campaignId],
    () => getCampaignStats(campaignId),
    {
      enabled: !!campaignId && enabled,
      onError: onSingleErrorResponse,
    }
  );
}

