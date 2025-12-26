import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const createRetailOrder = async (orderData) => {
  const { data } = await BeautyApi.createRetailOrder(orderData);
  return data;
};

export const useCreateRetailOrder = () => {
  return useMutation("beauty-create-retail-order", createRetailOrder);
};

