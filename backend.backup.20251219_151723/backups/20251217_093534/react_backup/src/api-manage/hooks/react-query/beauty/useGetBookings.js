import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getBookings = async (params) => {
  const { data } = await BeautyApi.getBookings(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetBookings(params, enabled = true) {
  return useQuery(
    ["beauty-bookings", params],
    () => getBookings(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

