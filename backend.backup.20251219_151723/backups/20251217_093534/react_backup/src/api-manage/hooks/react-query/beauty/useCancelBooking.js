import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const cancelBooking = async ({ id, cancellationReason }) => {
  const { data } = await BeautyApi.cancelBooking(id, cancellationReason);
  return data;
};

export const useCancelBooking = () => {
  return useMutation("beauty-cancel-booking", cancelBooking);
};

