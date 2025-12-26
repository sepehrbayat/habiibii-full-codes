import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const completeBooking = async (bookingId) => {
  const { data } = await BeautyVendorApi.completeBooking(bookingId);
  return data;
};

export const useCompleteBooking = () => {
  return useMutation("vendor-complete-booking", completeBooking);
};

