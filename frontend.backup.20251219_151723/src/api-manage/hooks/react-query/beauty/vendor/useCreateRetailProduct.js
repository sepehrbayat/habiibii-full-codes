import { useMutation } from "react-query";
import { BeautyVendorApi } from "../../../../another-formated-api/beautyVendorApi";

const createRetailProduct = async (productData) => {
  const { data } = await BeautyVendorApi.createRetailProduct(productData);
  return data;
};

export const useCreateRetailProduct = () => {
  return useMutation("vendor-create-retail-product", createRetailProduct);
};

