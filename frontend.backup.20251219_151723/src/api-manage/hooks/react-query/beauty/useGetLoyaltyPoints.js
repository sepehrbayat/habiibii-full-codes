import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getLoyaltyPoints = async () => {
  const { data } = await BeautyApi.getLoyaltyPoints();
  return data;
};

export default function useGetLoyaltyPoints(enabled = true) {
  return useQuery(
    "beauty-loyalty-points",
    getLoyaltyPoints,
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

