import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import WorkingHoursForm from "../../../../src/components/home/module-wise-components/beauty/vendor/WorkingHoursForm";
import { getServerSideProps } from "../../../index";

const VendorWorkingHours = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Working Hours` : "Loading..."}
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
            <WorkingHoursForm />
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorWorkingHours;
export { getServerSideProps };

