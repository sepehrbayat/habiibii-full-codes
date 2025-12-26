import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const deleteStaff = async (id) => {
  const { data } = await BeautyVendorApi.deleteStaff(id);
  return data;
};

export const useDeleteStaff = () => {
  return useMutation("vendor-delete-staff", deleteStaff);
};

