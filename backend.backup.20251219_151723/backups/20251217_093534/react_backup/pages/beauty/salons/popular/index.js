import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import SalonList from "../../../../src/components/home/module-wise-components/beauty/components/SalonList";
import CustomContainer from "../../../../src/components/container";
import useGetPopularSalons from "../../../../src/api-manage/hooks/react-query/beauty/useGetPopularSalons";
import { getServerSideProps } from "../../../index";

const PopularSalons = ({ configData, landingPageData }) => {
  const { data, isLoading } = useGetPopularSalons();

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Popular Beauty Salons` : "Loading..."}
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
            title="Popular Beauty Salons"
            salons={data?.data}
            isLoading={isLoading}
          />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default PopularSalons;
export { getServerSideProps };

