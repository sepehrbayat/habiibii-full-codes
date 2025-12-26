import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const updateStaff = async ({ id, staffData }) => {
  const { data } = await BeautyVendorApi.updateStaff(id, staffData);
  return data;
};

export const useUpdateStaff = () => {
  return useMutation("vendor-update-staff", updateStaff);
};

