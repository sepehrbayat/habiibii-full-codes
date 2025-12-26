import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const uploadDocuments = async (documents) => {
  const { data } = await BeautyVendorApi.uploadDocuments(documents);
  return data;
};

export const useUploadDocuments = () => {
  return useMutation("vendor-upload-documents", uploadDocuments);
};

