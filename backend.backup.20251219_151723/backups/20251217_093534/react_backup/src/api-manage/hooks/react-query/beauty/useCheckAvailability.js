import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const checkAvailability = async (availabilityData) => {
  const { data } = await BeautyApi.checkAvailability(availabilityData);
  return data;
};

export const useCheckAvailability = () => {
  return useMutation("beauty-check-availability", checkAvailability);
};

