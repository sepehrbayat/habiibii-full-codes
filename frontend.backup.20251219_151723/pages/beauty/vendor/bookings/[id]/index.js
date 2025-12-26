import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import VendorBookingDetails from "../../../../../src/components/home/module-wise-components/beauty/vendor/VendorBookingDetails";
import { useRouter } from "next/router";
import { getServerSideProps } from "../../../../index";

const VendorBookingDetail = ({ configData, landingPageData }) => {
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
        <VendorAuthGuard>
          <VendorLayout configData={configData}>
            <VendorBookingDetails bookingId={id} />
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorBookingDetail;
export { getServerSideProps };

