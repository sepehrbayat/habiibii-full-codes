import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import DocumentsUpload from "../../../../src/components/home/module-wise-components/beauty/vendor/DocumentsUpload";
import { getServerSideProps } from "../../../index";

const VendorDocuments = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Documents Upload` : "Loading..."}
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
            <DocumentsUpload />
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorDocuments;
export { getServerSideProps };

