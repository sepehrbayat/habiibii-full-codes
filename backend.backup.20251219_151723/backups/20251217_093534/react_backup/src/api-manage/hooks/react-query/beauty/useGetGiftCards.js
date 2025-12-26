import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getGiftCards = async (params) => {
  const { data } = await BeautyApi.getGiftCards(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetGiftCards(params, enabled = true) {
  return useQuery(
    ["beauty-gift-cards", params],
    () => getGiftCards(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

