import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getLoyaltyCampaigns = async (params) => {
  const { data } = await BeautyApi.getLoyaltyCampaigns(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetLoyaltyCampaigns(params, enabled = true) {
  return useQuery(
    ["beauty-loyalty-campaigns", params],
    () => getLoyaltyCampaigns(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

