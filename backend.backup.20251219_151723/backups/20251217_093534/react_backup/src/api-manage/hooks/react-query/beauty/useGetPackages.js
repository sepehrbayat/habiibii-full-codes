import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getPackages = async (params) => {
  const { data } = await BeautyApi.getPackages(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetPackages(params, enabled = true) {
  return useQuery(
    ["beauty-packages", params],
    () => getPackages(params),
    {
      enabled,
      onError: onSingleErrorResponse,
    }
  );
}

