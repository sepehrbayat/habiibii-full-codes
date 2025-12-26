import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const createBooking = async (bookingData) => {
  const { data } = await BeautyApi.createBooking(bookingData);
  return data;
};

export const useCreateBooking = () => {
  return useMutation("beauty-create-booking", createBooking);
};

