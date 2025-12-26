import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getStaffDetails = async (id) => {
  const { data } = await BeautyVendorApi.getStaffDetails(id);
  return data;
};

export const useGetStaffDetails = (id, enabled = true) => {
  return useQuery(
    ["vendor-staff-details", id],
    () => getStaffDetails(id),
    {
      enabled: !!id && enabled,
      onError: onSingleErrorResponse,
    }
  );
}

