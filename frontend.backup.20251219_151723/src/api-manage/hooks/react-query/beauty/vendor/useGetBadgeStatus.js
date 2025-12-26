import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getBadgeStatus = async () => {
  const { data } = await BeautyVendorApi.getBadgeStatus();
  return data;
};

export const useGetBadgeStatus = (enabled = true) => {
  return useQuery(
    ["vendor-badge-status"],
    () => getBadgeStatus(),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

