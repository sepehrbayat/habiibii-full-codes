import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const toggleStaffStatus = async (id) => {
  const { data } = await BeautyVendorApi.toggleStaffStatus(id);
  return data;
};

export const useToggleStaffStatus = () => {
  return useMutation("vendor-toggle-staff-status", toggleStaffStatus);
};

