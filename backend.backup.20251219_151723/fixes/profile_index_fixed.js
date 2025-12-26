import React, { useEffect, useState } from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../src/components/layout/MainLayout";
import { useTranslation } from "react-i18next";
import { NoSsr } from "@mui/material";
import AuthGuard from "../../src/components/route-guard/AuthGuard";
import { useRouter } from "next/router";
import SEO from "../../src/components/seo";
import UserInformation from "../../src/components/user-information/UserInformation";
import jwt from "base-64";
import { useDispatch, useSelector } from "react-redux";
import { useGetConfigData } from "../../src/api-manage/hooks/useGetConfigData";
import { setConfigData } from "../../src/redux/slices/configData";
import ErrorBoundary from "../../src/components/ErrorBoundary";

const Index = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const router = useRouter();
  const { page, orderId, token } = router.query;
  const [attributeId, setAttributeId] = useState("");
  const { landingPageData, configData } = useSelector(
    (state) => state.configData
  );
  const { data: dataConfig, refetch: configRefetch } = useGetConfigData();
  
  useEffect(() => {
    if (!configData) {
      configRefetch();
    }
  }, [configData]);
  
  useEffect(() => {
    if (dataConfig) {
      dispatch(setConfigData(dataConfig));
    }
  }, [dataConfig]);
  
  useEffect(() => {
    // Only process token if it exists and is a valid string
    // فقط پردازش token اگر وجود داشته باشد و یک رشته معتبر باشد
    if (token && typeof token === "string" && token.trim() !== "") {
      try {
        // Attempt to decode the Base64 token
        // تلاش برای decode کردن token Base64
        const decodedToken = jwt.decode(token);

        // Check if decodedToken is a valid string
        // بررسی اینکه آیا decodedToken یک رشته معتبر است
        if (typeof decodedToken === "string" && decodedToken.trim() !== "") {
          // Assuming decodedToken is in the format: "key1=value1&&key2=value2&&..."
          // فرض بر این است که decodedToken در فرمت "key1=value1&&key2=value2&&..." است
          const keyValuePairs = decodedToken.split("&&");

          // Loop through the key-value pairs to find the one with attribute_id
          // حلقه زدن در جفت‌های کلید-مقدار برای پیدا کردن attribute_id
          for (const pair of keyValuePairs) {
            if (pair && pair.includes("=")) {
              const [key, value] = pair.split("=");
              if (key === "attribute_id" && value) {
                setAttributeId(value);
                return; // Exit the loop when attribute_id is found
              }
            }
          }
        }
      } catch (error) {
        // Silently handle decoding errors - token might be in different format
        // مدیریت خاموش خطاهای decode - token ممکن است در فرمت متفاوتی باشد
        console.warn("Error decoding token:", error);
      }
    }
    // Removed console.error for missing token - it's not always required
    // حذف console.error برای token گمشده - همیشه لازم نیست
  }, [token]);
  
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Profile` : "Loading..."}
        image={configData?.fav_icon_full_url}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <NoSsr>
          <ErrorBoundary>
            <AuthGuard from={router.pathname.replace("/", "")}>
              <UserInformation
                page={page}
                configData={configData}
                orderId={orderId ?? attributeId}
              />
            </AuthGuard>
          </ErrorBoundary>
        </NoSsr>
      </MainLayout>
    </>
  );
};

export default Index;

