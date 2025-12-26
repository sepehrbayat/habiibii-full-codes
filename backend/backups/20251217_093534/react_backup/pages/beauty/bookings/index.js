import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../src/components/layout/MainLayout";
import SEO from "../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import BookingList from "../../../src/components/home/module-wise-components/beauty/components/BookingList";
import CustomContainer from "../../../src/components/container";
import { getServerSideProps } from "../../index";

const MyBookings = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `My Beauty Bookings` : "Loading..."}
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
          <BookingList />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default MyBookings;
export { getServerSideProps };

