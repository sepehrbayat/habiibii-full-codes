import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import RetailProducts from "../../../../src/components/home/module-wise-components/beauty/components/RetailProducts";
import CustomContainer from "../../../../src/components/container";
import { getServerSideProps } from "../../../index";

const RetailProductsPage = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Beauty Retail Products` : "Loading..."}
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
          <RetailProducts />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default RetailProductsPage;
export { getServerSideProps };

