import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getSalonReviews = async (salonId, params) => {
  const { data } = await BeautyApi.getSalonReviews(salonId, params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetSalonReviews(salonId, params = {}, enabled = true) {
  return useQuery(
    ["beauty-salon-reviews", salonId, params],
    () => getSalonReviews(salonId, params),
    {
      enabled: enabled && !!salonId,
      onError: onSingleErrorResponse,
    }
  );
}

