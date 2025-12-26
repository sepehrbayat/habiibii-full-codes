import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getTopRatedSalons = async () => {
  const { data } = await BeautyApi.getTopRatedSalons();
  return data;
};

export default function useGetTopRatedSalons(enabled = true) {
  return useQuery(
    "beauty-top-rated-salons",
    getTopRatedSalons,
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

