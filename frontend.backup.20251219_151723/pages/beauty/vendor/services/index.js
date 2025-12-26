import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import ServiceList from "../../../../src/components/home/module-wise-components/beauty/vendor/ServiceList";
import { getServerSideProps } from "../../../index";

const VendorServices = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Vendor Services` : "Loading..."}
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
            <ServiceList />
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorServices;
export { getServerSideProps };

