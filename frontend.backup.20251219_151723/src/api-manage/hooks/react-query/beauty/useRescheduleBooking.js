import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const rescheduleBooking = async ({ id, ...rescheduleData }) => {
  const { data } = await BeautyApi.rescheduleBooking(id, rescheduleData);
  return data;
};

export const useRescheduleBooking = () => {
  return useMutation("beauty-reschedule-booking", rescheduleBooking);
};

