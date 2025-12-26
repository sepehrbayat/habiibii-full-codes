import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getRetailProducts = async (params) => {
  const { data } = await BeautyApi.getRetailProducts(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetRetailProducts(params, enabled = true) {
  return useQuery(
    ["beauty-retail-products", params],
    () => getRetailProducts(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

