import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getUserReviews = async (params) => {
  const { data } = await BeautyApi.getReviews(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetUserReviews(params, enabled = true) {
  return useQuery(
    ["beauty-user-reviews", params],
    () => getUserReviews(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

