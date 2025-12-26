import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import SalonList from "../../../../src/components/home/module-wise-components/beauty/components/SalonList";
import CustomContainer from "../../../../src/components/container";
import { useQuery } from "react-query";
import { BeautyApi } from "../../../../src/api-manage/another-formated-api/beautyApi";
import { getServerSideProps } from "../../../index";

const TrendingClinics = ({ configData, landingPageData }) => {
  const { data, isLoading } = useQuery(
    "beauty-trending-clinics",
    () => BeautyApi.getTrendingClinics(),
    { enabled: true }
  );

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Trending Clinics` : "Loading..."}
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
          <SalonList
            title="Trending Clinics"
            salons={data?.data}
            isLoading={isLoading}
          />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default TrendingClinics;
export { getServerSideProps };

