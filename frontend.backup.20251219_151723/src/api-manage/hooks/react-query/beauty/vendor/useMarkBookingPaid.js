import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const markBookingPaid = async (bookingId) => {
  const { data } = await BeautyVendorApi.markBookingPaid(bookingId);
  return data;
};

export const useMarkBookingPaid = () => {
  return useMutation("vendor-mark-booking-paid", markBookingPaid);
};

