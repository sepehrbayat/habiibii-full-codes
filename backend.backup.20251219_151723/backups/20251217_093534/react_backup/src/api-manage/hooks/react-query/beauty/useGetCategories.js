import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getCategories = async () => {
  const { data } = await BeautyApi.getCategories();
  return data;
};

export default function useGetCategories(enabled = true) {
  return useQuery(
    "beauty-categories",
    getCategories,
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

