import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const deleteCalendarBlock = async (id) => {
  const { data } = await BeautyVendorApi.deleteCalendarBlock(id);
  return data;
};

export const useDeleteCalendarBlock = () => {
  return useMutation("vendor-delete-calendar-block", deleteCalendarBlock);
};

