import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const updateService = async ({ id, serviceData }) => {
  const { data } = await BeautyVendorApi.updateService(id, serviceData);
  return data;
};

export const useUpdateService = () => {
  return useMutation("vendor-update-service", updateService);
};

