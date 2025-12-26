import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import BookingDetails from "../../../../src/components/home/module-wise-components/beauty/components/BookingDetails";
import CustomContainer from "../../../../src/components/container";
import { getServerSideProps } from "../../../index";
import { useRouter } from "next/router";

const BookingDetailPage = ({ configData, landingPageData }) => {
  const router = useRouter();
  const { id } = router.query;

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Booking Details` : "Loading..."}
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
          <BookingDetails bookingId={id} />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default BookingDetailPage;
export { getServerSideProps };

