import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getConsultations = async (params) => {
  const { data } = await BeautyApi.getConsultations(params);
  return normalizeBeautyResponse(data, params);
};

export default function useGetConsultations(params, enabled = true) {
  return useQuery(
    ["beauty-consultations", params],
    () => getConsultations(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}

