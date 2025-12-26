import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const createStaff = async (staffData) => {
  const { data } = await BeautyVendorApi.createStaff(staffData);
  return data;
};

export const useCreateStaff = () => {
  return useMutation("vendor-create-staff", createStaff);
};

