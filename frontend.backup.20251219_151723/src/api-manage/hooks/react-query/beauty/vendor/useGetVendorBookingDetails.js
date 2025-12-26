import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getVendorBookingDetails = async (bookingId) => {
  const { data } = await BeautyVendorApi.getBookingDetails(bookingId);
  return data;
};

export const useGetVendorBookingDetails = (bookingId, enabled = true) => {
  return useQuery(
    ["vendor-booking-details", bookingId],
    () => getVendorBookingDetails(bookingId),
    {
      enabled: !!bookingId && enabled,
      onError: onSingleErrorResponse,
    }
  );
}

