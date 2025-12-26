import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";
import { normalizeBeautyResponse } from "../../../utils/beautyResponseNormalizer";

const getServiceSuggestions = async (serviceId, salonId) => {
  const { data } = await BeautyApi.getServiceSuggestions(serviceId, salonId);
  return normalizeBeautyResponse(data);
};

export default function useGetServiceSuggestions(serviceId, salonId, enabled = true) {
  return useQuery(
    ["beauty-service-suggestions", serviceId, salonId],
    () => getServiceSuggestions(serviceId, salonId),
    {
      enabled: enabled && !!serviceId,
      onError: onSingleErrorResponse,
    }
  );
}

