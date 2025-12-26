import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../../utils/beautyResponseNormalizer";

const getVendorPackages = async (params) => {
  const { data } = await BeautyVendorApi.listPackages(params);
  return normalizeBeautyResponse(data, params);
};

export const useGetVendorPackages = (params, enabled = true) => {
  return useQuery(
    ["vendor-packages", params],
    () => getVendorPackages(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

