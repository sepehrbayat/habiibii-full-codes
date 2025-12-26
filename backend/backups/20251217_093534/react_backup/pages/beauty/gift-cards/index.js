import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../src/components/layout/MainLayout";
import SEO from "../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import GiftCardList from "../../../src/components/home/module-wise-components/beauty/components/GiftCardList";
import CustomContainer from "../../../src/components/container";
import { getServerSideProps } from "../../index";

const GiftCards = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Gift Cards` : "Loading..."}
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
          <GiftCardList />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default GiftCards;
export { getServerSideProps };

