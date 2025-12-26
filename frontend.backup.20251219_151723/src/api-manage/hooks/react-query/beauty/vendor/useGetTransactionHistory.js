import { useQuery } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../api-error-response/ErrorResponses";

const getTransactionHistory = async (params) => {
  const { data } = await BeautyVendorApi.getTransactionHistory(params);
  return data;
};

export const useGetTransactionHistory = (params, enabled = true) => {
  return useQuery(
    ["vendor-transaction-history", params],
    () => getTransactionHistory(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

