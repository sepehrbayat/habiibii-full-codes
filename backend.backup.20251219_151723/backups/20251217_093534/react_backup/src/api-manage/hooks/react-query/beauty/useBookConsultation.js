import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const bookConsultation = async (consultationData) => {
  const { data } = await BeautyApi.bookConsultation(consultationData);
  return data;
};

export const useBookConsultation = () => {
  return useMutation("beauty-book-consultation", bookConsultation);
};

