import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getSalons = async (params) => {
  const { data } = await BeautyApi.searchSalons(params);
  return data;
};

export default function useGetSalons(params, enabled = true) {
  return useQuery(
    ["beauty-salons", params],
    () => getSalons(params),
    {
      enabled: enabled && !!params,
      onError: onSingleErrorResponse,
    }
  );
}

