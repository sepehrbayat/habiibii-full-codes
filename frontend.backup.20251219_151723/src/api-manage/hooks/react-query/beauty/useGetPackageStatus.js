import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { normalizeBeautyItemResponse } from "../../../utils/beautyResponseNormalizer";

const useGetPackageStatus = (packageId, options = {}) => {
  return useQuery(
    ["beauty-package-status", packageId],
    async () => {
      const { data } = await BeautyApi.getPackageStatus(packageId);
      return normalizeBeautyItemResponse(data);
    },
    {
      enabled: !!packageId && options.enabled !== false,
      ...options,
    }
  );
};

export default useGetPackageStatus;

