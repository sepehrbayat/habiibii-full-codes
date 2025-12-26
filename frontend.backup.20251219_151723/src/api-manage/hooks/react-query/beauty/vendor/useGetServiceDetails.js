import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getServiceDetails = async (id) => {
  const { data } = await BeautyVendorApi.getServiceDetails(id);
  return data;
};

export const useGetServiceDetails = (id, enabled = true) => {
  return useQuery(
    ["vendor-service-details", id],
    () => getServiceDetails(id),
    {
      enabled: !!id && enabled,
      onError: onSingleErrorResponse,
    }
  );
}

