import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../../utils/beautyResponseNormalizer";

const getVendorGiftCards = async (params) => {
  const { data } = await BeautyVendorApi.listGiftCards(params);
  return normalizeBeautyResponse(data, params);
};

export const useGetVendorGiftCards = (params, enabled = true) => {
  return useQuery(
    ["vendor-gift-cards", params],
    () => getVendorGiftCards(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

