import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const confirmBooking = async (bookingId) => {
  const { data } = await BeautyVendorApi.confirmBooking(bookingId);
  return data;
};

export const useConfirmBooking = () => {
  return useMutation("vendor-confirm-booking", confirmBooking);
};

