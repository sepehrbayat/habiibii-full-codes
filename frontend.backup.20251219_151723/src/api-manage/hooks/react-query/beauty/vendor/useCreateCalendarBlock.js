import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const createCalendarBlock = async (blockData) => {
  const { data } = await BeautyVendorApi.createCalendarBlock(blockData);
  return data;
};

export const useCreateCalendarBlock = () => {
  return useMutation("vendor-create-calendar-block", createCalendarBlock);
};

