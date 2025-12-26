import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getVendorServices = async (params) => {
  const { data } = await BeautyVendorApi.listServices(params);
  return data;
};

export const useGetVendorServices = (params, enabled = true) => {
  return useQuery(
    ["vendor-services", params],
    () => getVendorServices(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

