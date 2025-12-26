import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const purchasePackage = async ({ id, paymentMethod }) => {
  const { data } = await BeautyApi.purchasePackage(id, paymentMethod);
  return data;
};

export const usePurchasePackage = () => {
  return useMutation("beauty-purchase-package", purchasePackage);
};

