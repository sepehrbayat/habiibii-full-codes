import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getSalonDetails = async (id) => {
  const { data } = await BeautyApi.getSalonDetails(id);
  return data;
};

export default function useGetSalonDetails(id, enabled = true) {
  return useQuery(
    ["beauty-salon-details", id],
    () => getSalonDetails(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}

