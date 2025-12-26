import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyItemResponse } from "../../../utils/beautyResponseNormalizer";

const getRetailOrderDetails = async (id) => {
  const { data } = await BeautyApi.getRetailOrderDetails(id);
  return normalizeBeautyItemResponse(data);
};

export default function useGetRetailOrderDetails(id, enabled = true) {
  return useQuery(
    ["beauty-retail-order-details", id],
    () => getRetailOrderDetails(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}

