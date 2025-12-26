import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../../utils/beautyResponseNormalizer";

const getVendorRetailOrders = async (params) => {
  const { data } = await BeautyVendorApi.listRetailOrders(params);
  return normalizeBeautyResponse(data, params);
};

export const useGetVendorRetailOrders = (params, enabled = true) => {
  return useQuery(
    ["vendor-retail-orders", params],
    () => getVendorRetailOrders(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

