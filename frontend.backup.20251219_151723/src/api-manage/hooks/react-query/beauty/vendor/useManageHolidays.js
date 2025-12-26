import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const manageHolidays = async ({ action, holidays }) => {
  const { data } = await BeautyVendorApi.manageHolidays(action, holidays);
  return data;
};

const useManageHolidays = () => {
  return useMutation("vendor-manage-holidays", manageHolidays);
};

export { useManageHolidays };

