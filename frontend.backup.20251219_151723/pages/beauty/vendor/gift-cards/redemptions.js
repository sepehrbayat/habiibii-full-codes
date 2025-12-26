import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import RedemptionHistory from "../../../../src/components/home/module-wise-components/beauty/vendor/RedemptionHistory";
import { getServerSideProps } from "../../../index";

const VendorRedemptions = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Redemption History` : "Loading..."}
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
            <RedemptionHistory />
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorRedemptions;
export { getServerSideProps };

