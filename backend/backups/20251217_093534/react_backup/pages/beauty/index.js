import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../src/components/layout/MainLayout";
import SEO from "../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import { getServerSideProps as baseGetServerSideProps } from "../index";
import CustomContainer from "../../src/components/container";
import BeautyDashboard from "../../src/components/home/module-wise-components/beauty/components/BeautyDashboard";

const BeautyIndex = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Beauty Services` : "Loading..."}
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
          <BeautyDashboard />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default BeautyIndex;
export const getServerSideProps = baseGetServerSideProps;

