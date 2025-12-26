import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getPointsHistory = async (params) => {
  const { data } = await BeautyVendorApi.getPointsHistory(params);
  return data;
};

export const useGetPointsHistory = (params, enabled = true) => {
  return useQuery(
    ["vendor-points-history", params],
    () => getPointsHistory(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

