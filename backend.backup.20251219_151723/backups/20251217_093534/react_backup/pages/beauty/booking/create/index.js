import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import BookingForm from "../../../../src/components/home/module-wise-components/beauty/components/BookingForm";
import CustomContainer from "../../../../src/components/container";
import { getServerSideProps } from "../../../index";

const CreateBooking = ({ configData, landingPageData }) => {
  const seoImage =
    configData?.fav_icon_full_url ||
    (configData?.logo_storage && configData?.fav_icon
      ? `${getImageUrl({ value: configData.logo_storage }, "business_logo_url", configData)}/${configData.fav_icon}`
      : "/favicon.ico");
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Create Booking` : "Loading..."}
        image={seoImage}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <CustomContainer>
          <BookingForm />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default CreateBooking;
export { getServerSideProps };

