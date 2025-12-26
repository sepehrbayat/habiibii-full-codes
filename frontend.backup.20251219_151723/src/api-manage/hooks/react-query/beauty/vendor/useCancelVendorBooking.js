import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const cancelVendorBooking = async ({ bookingId, cancellationReason }) => {
  const { data } = await BeautyVendorApi.cancelBooking(bookingId, cancellationReason);
  return data;
};

export const useCancelVendorBooking = () => {
  return useMutation("vendor-cancel-booking", cancelVendorBooking);
};

