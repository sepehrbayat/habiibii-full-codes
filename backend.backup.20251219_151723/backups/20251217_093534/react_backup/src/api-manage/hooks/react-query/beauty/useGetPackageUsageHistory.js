import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getPackageUsageHistory = async (id) => {
  const { data } = await BeautyApi.getPackageUsageHistory(id);
  return normalizeBeautyResponse(data);
};

export default function useGetPackageUsageHistory(id, enabled = true) {
  return useQuery(
    ["beauty-package-usage-history", id],
    () => getPackageUsageHistory(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}

