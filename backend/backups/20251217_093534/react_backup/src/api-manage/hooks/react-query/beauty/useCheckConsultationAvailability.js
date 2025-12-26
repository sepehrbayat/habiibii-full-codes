import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const checkConsultationAvailability = async (availabilityData) => {
  const { data } = await BeautyApi.checkConsultationAvailability(availabilityData);
  return data;
};

export const useCheckConsultationAvailability = () => {
  return useMutation("beauty-check-consultation-availability", checkConsultationAvailability);
};

