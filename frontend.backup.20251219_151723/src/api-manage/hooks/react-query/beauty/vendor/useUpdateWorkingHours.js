import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const updateWorkingHours = async (workingHours) => {
  const { data } = await BeautyVendorApi.updateWorkingHours(workingHours);
  return data;
};

export const useUpdateWorkingHours = () => {
  return useMutation("vendor-update-working-hours", updateWorkingHours);
};

