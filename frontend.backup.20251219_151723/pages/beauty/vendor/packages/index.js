import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import VendorLayout from "../../../../src/components/layout/VendorLayout";
import VendorAuthGuard from "../../../../src/components/route-guard/VendorAuthGuard";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import PackageList from "../../../../src/components/home/module-wise-components/beauty/vendor/PackageList";
import PackageUsageStats from "../../../../src/components/home/module-wise-components/beauty/vendor/PackageUsageStats";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { getServerSideProps } from "../../../index";

const VendorPackages = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Packages` : "Loading..."}
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
            <CustomStackFullWidth spacing={4} sx={{ py: 4 }}>
              <PackageList />
              <PackageUsageStats />
            </CustomStackFullWidth>
          </VendorLayout>
        </VendorAuthGuard>
      </MainLayout>
    </>
  );
};

export default VendorPackages;
export { getServerSideProps };

