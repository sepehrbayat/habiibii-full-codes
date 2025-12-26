import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const toggleServiceStatus = async (id) => {
  const { data } = await BeautyVendorApi.toggleServiceStatus(id);
  return data;
};

export const useToggleServiceStatus = () => {
  return useMutation("vendor-toggle-service-status", toggleServiceStatus);
};

