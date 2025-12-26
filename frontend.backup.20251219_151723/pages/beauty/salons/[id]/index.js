import React, { useEffect } from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import { useDispatch } from "react-redux";
import Router from "next/router";
import { setConfigData } from "redux/slices/configData";
import SEO from "../../../../src/components/seo";
import { NoSsr } from "@mui/material";
import useScrollToTop from "api-manage/hooks/custom-hooks/useScrollToTop";
import SalonDetails from "../../../../src/components/home/module-wise-components/beauty/components/SalonDetails";
import { getServerSideProps as baseGetServerSideProps } from "../../../index";

const SalonPage = ({ configData, salonDetails }) => {
  const dispatch = useDispatch();
  useScrollToTop();

  const metaTitle = `${salonDetails?.name || "Salon"} - ${configData?.business_name}`;
  const metaImage = salonDetails?.image || salonDetails?.store?.image;

  useEffect(() => {
    if (!configData || Object.keys(configData).length === 0) {
      Router.replace("/404");
    } else if (configData?.maintenance_mode) {
      Router.replace("/maintainance");
    } else {
      dispatch(setConfigData(configData));
    }
  }, [configData, dispatch]);

  return (
    <>
      <CssBaseline />
      <SEO
        title={metaTitle}
        image={metaImage}
        businessName={configData?.business_name}
        description={salonDetails?.description}
        configData={configData}
      />
      <MainLayout configData={configData}>
        <NoSsr>
          <SalonDetails salonDetails={salonDetails} configData={configData} />
        </NoSsr>
      </MainLayout>
    </>
  );
};

export default SalonPage;

export const getServerSideProps = async (context) => {
  const { id: salonId } = context.query;
  const { req } = context;
  const language = req.cookies.languageSetting || "en";

  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 5000);

  try {
    const baseUrl = process.env.NEXT_PUBLIC_BASE_URL;
    const origin = process.env.NEXT_CLIENT_HOST_URL;

    const headersCommon = {
      "X-software-id": 33571750,
      "X-server": "server",
      origin,
      "X-localization": language,
    };

    const configRes = await fetch(`${baseUrl}/api/v1/config`, {
      method: "GET",
      headers: headersCommon,
      signal: controller.signal,
    });

    const salonDetailsRes = await fetch(`${baseUrl}/api/v1/beautybooking/salons/${salonId}`, {
      method: "GET",
      headers: headersCommon,
      signal: controller.signal,
    });

    clearTimeout(timeout);

    if (!configRes.ok || !salonDetailsRes.ok) {
      throw new Error("One or more API calls failed.");
    }

    const configData = await configRes.json();
    const salonDetails = await salonDetailsRes.json();

    const baseProps = await baseGetServerSideProps(context);

    return {
      props: {
        ...baseProps.props,
        configData,
        salonDetails: salonDetails?.data || salonDetails,
      },
    };
  } catch (error) {
    clearTimeout(timeout);
    console.error("SSR fetch failed:", error.message);
    return {
      notFound: true,
    };
  }
};

