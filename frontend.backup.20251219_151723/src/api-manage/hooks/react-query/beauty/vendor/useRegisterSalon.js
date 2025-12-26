import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const registerSalon = async (salonData) => {
  const { data } = await BeautyVendorApi.registerSalon(salonData);
  return data;
};

export const useRegisterSalon = () => {
  return useMutation("vendor-register-salon", registerSalon);
};

