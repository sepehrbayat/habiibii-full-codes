import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getPopularSalons = async () => {
  const { data } = await BeautyApi.getPopularSalons();
  return data;
};

export default function useGetPopularSalons(enabled = true) {
  return useQuery(
    "beauty-popular-salons",
    getPopularSalons,
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

