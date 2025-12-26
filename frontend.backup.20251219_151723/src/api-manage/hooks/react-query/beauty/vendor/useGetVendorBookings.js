import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../../utils/beautyResponseNormalizer";

const getVendorBookings = async (params) => {
  const { data } = await BeautyVendorApi.listBookings(params);
  return normalizeBeautyResponse(data, params);
};

export const useGetVendorBookings = (params, enabled = true) => {
  return useQuery(
    ["vendor-bookings", params],
    () => getVendorBookings(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

