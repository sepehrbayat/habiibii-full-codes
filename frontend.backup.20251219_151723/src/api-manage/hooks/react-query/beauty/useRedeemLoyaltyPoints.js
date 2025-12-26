import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const redeemLoyaltyPoints = async (redeemData) => {
  const { data } = await BeautyApi.redeemLoyaltyPoints(redeemData);
  return data;
};

export const useRedeemLoyaltyPoints = () => {
  return useMutation("beauty-redeem-loyalty-points", redeemLoyaltyPoints);
};

