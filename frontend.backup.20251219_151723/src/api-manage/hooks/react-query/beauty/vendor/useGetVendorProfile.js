import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getVendorProfile = async () => {
  const { data } = await BeautyVendorApi.getVendorProfile();
  return data;
};

export const useGetVendorProfile = (enabled = true) => {
  return useQuery(
    ["vendor-profile"],
    () => getVendorProfile(),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

