import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getPackageUsageStats = async (params) => {
  const { data } = await BeautyVendorApi.getPackageUsageStats(params);
  return data;
};

export const useGetPackageUsageStats = (params, enabled = true) => {
  return useQuery(
    ["vendor-package-usage-stats", params],
    () => getPackageUsageStats(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

