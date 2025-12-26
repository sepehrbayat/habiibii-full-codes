import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const redeemGiftCard = async (code) => {
  const { data } = await BeautyApi.redeemGiftCard(code);
  return data;
};

export const useRedeemGiftCard = () => {
  return useMutation("beauty-redeem-gift-card", redeemGiftCard);
};

