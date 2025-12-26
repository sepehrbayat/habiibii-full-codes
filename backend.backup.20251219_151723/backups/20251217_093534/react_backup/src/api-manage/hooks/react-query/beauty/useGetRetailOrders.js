import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getRetailOrders = async (params) => {
  const { data } = await BeautyApi.getRetailOrders(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetRetailOrders(params, enabled = true) {
  return useQuery(
    ["beauty-retail-orders", params],
    () => getRetailOrders(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

