import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const createService = async (serviceData) => {
  const { data } = await BeautyVendorApi.createService(serviceData);
  return data;
};

export const useCreateService = () => {
  return useMutation("vendor-create-service", createService);
};

