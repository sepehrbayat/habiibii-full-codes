import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../../utils/beautyResponseNormalizer";

const getVendorRetailProducts = async (params) => {
  const { data } = await BeautyVendorApi.listRetailProducts(params);
  return normalizeBeautyResponse(data, params);
};

export const useGetVendorRetailProducts = (params, enabled = true) => {
  return useQuery(
    ["vendor-retail-products", params],
    () => getVendorRetailProducts(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

