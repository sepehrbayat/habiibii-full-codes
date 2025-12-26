import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getBookingDetails = async (id) => {
  const { data } = await BeautyApi.getBookingDetails(id);
  return data;
};

export default function useGetBookingDetails(id, enabled = true) {
  return useQuery(
    ["beauty-booking-details", id],
    () => getBookingDetails(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}

