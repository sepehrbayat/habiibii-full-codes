import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getCalendarAvailability = async (params) => {
  const { data } = await BeautyVendorApi.getCalendarAvailability(params);
  return data;
};

export const useGetCalendarAvailability = (params, enabled = true) => {
  return useQuery(
    ["vendor-calendar-availability", params],
    () => getCalendarAvailability(params),
    {
      enabled: !!params?.date && enabled,
      onError: onSingleErrorResponse,
    }
  );
}

