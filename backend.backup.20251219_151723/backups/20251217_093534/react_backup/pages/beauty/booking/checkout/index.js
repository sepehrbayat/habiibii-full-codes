import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import CustomContainer from "../../../../src/components/container";
import { getServerSideProps } from "../../../index";
import { useRouter } from "next/router";
import BookingCheckout from "../../../../src/components/home/module-wise-components/beauty/components/BookingCheckout";

const BookingCheckoutPage = ({ configData, landingPageData }) => {
  const router = useRouter();
  const { booking_id } = router.query;

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Booking Checkout` : "Loading..."}
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
          <BookingCheckout bookingId={booking_id} />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default BookingCheckoutPage;
export { getServerSideProps };

