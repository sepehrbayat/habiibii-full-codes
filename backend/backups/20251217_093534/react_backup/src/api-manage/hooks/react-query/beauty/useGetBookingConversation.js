import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { normalizeBeautyItemResponse } from "../../../utils/beautyResponseNormalizer";

const useGetBookingConversation = (bookingId, options = {}) => {
  return useQuery(
    ["beauty-booking-conversation", bookingId],
    async () => {
      const { data } = await BeautyApi.getBookingConversation(bookingId);
      return normalizeBeautyItemResponse(data);
    },
    {
      enabled: !!bookingId && options.enabled !== false,
      ...options,
    }
  );
};

export default useGetBookingConversation;

