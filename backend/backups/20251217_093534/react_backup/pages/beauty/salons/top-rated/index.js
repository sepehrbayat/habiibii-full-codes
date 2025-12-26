import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import CustomContainer from "../../../../src/components/container";
import MonthlyTopRatedSalons from "../../../../src/components/home/module-wise-components/beauty/components/MonthlyTopRatedSalons";
import { getServerSideProps } from "../../../index";

const TopRatedSalonsPage = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Top-Rated Salons` : "Loading..."}
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
          <MonthlyTopRatedSalons />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default TopRatedSalonsPage;
export { getServerSideProps };

