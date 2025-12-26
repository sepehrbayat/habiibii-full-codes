import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import { Box, Typography, useMediaQuery, useTheme } from "@mui/material";
import { useTranslation } from "react-i18next";

import MainLayout from "../../src/components/layout/MainLayout";
import CustomContainer from "../../src/components/container";
import SEO from "../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import ModuleSelectionRaw from "../../src/components/landing-page/hero-section/module-selection/ModuleSelectionRaw";
import { getServerSideProps as baseGetServerSideProps } from "../index";

const ModuleSelectPage = ({ configData, landingPageData }) => {
  const theme = useTheme();
  const isSmall = useMediaQuery(theme.breakpoints.down("sm"));
  const { t } = useTranslation();

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? t("Choose your module") : "Loading..."}
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
          <Box sx={{ py: { xs: 3, md: 6 }, maxWidth: 900, mx: "auto" }}>
            <Typography variant="h5" fontWeight={700} sx={{ mb: 1 }}>
              {t("Choose your module")}
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              {t("Pick the module you want to continue with.")}
            </Typography>
            <ModuleSelectionRaw isSmall={isSmall} />
          </Box>
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export const getServerSideProps = baseGetServerSideProps;

export default ModuleSelectPage;

