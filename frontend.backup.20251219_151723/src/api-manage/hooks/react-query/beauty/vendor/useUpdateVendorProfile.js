import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const updateVendorProfile = async (profileData) => {
  const { data } = await BeautyVendorApi.updateProfile(profileData);
  return data;
};

export const useUpdateVendorProfile = () => {
  return useMutation("vendor-update-profile", updateVendorProfile);
};

