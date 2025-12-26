import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const deleteService = async (id) => {
  const { data } = await BeautyVendorApi.deleteService(id);
  return data;
};

export const useDeleteService = () => {
  return useMutation("vendor-delete-service", deleteService);
};

