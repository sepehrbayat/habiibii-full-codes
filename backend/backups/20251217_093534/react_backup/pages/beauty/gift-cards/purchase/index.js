import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import CustomContainer from "../../../../src/components/container";
import GiftCardPurchase from "../../../../src/components/home/module-wise-components/beauty/components/GiftCardPurchase";
import { getServerSideProps } from "../../../index";

const GiftCardPurchasePage = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Purchase Beauty Gift Card` : "Loading..."}
        image={`${getImageUrl(
          { value: configData?.logo_storage },
          "business_logo_url",
          configData
        )}/${configData?.fav_icon}`}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <CustomContainer>
          <GiftCardPurchase />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default GiftCardPurchasePage;
export { getServerSideProps };

