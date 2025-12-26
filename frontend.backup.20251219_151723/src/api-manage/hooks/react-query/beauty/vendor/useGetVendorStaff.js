import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getVendorStaff = async (params) => {
  const { data } = await BeautyVendorApi.listStaff(params);
  return data;
};

export const useGetVendorStaff = (params, enabled = true) => {
  return useQuery(
    ["vendor-staff", params],
    () => getVendorStaff(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

