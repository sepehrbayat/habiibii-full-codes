import "../src/styles/globals.css";
import "../src/styles/nprogress.css";
import { CacheProvider } from "@emotion/react";
import { Provider as ReduxProvider } from "react-redux";
import createEmotionCache from "../src/utils/create-emotion-cache";
import { store } from "redux/store";
import { ThemeProvider } from "@mui/material/styles";
import { createTheme } from "theme";

import CssBaseline from "@mui/material/CssBaseline";
import { RTL } from "components/rtl";
import { Toaster } from "react-hot-toast";
import { getServerSideProps } from "./index";
import { SettingsConsumer, SettingsProvider } from "contexts/settings-context";
import "../src/language/i18n";
import { QueryClient, QueryClientProvider } from "react-query";
import { ReactQueryDevtools } from "react-query/devtools";
import nProgress from "nprogress";
import Router from "next/router";
import { persistStore } from "redux-persist";
import { useTranslation } from "react-i18next";
import useScrollToTop from "../src/api-manage/hooks/custom-hooks/useScrollToTop";
import { useEffect } from "react";
import { VendorAuthProvider } from "../src/contexts/vendor-auth-context";
import ErrorBoundary from "../src/components/ErrorBoundary";

Router.events.on("routeChangeStart", nProgress.start);
Router.events.on("routeChangeError", nProgress.done);
Router.events.on("routeChangeComplete", nProgress.done);
export const currentVersion = process.env.NEXT_PUBLIC_SITE_VERSION;
const clientSideEmotionCache = createEmotionCache();
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      cacheTime: 1000 * 60 * 5, // 5 minutes
      staleTime: 1000 * 60 * 2, // 2 minutes
      // Add error handling for queries
      // افزودن مدیریت خطا برای queries
      onError: (error) => {
        console.error("React Query error:", error);
      },
    },
    mutations: {
      // Add error handling for mutations
      // افزودن مدیریت خطا برای mutations
      onError: (error) => {
        console.error("React Query mutation error:", error);
      },
    },
  },
});

function MyApp(props) {
  const {
    Component,
    emotionCache = clientSideEmotionCache,
    pageProps,
  } = props;
  const getLayout = Component.getLayout ?? ((page) => page);
  const { t } = useTranslation();

  // Persist store
  let persistor = persistStore(store);

  // Version check with error handling
  // بررسی نسخه با مدیریت خطا
  useEffect(() => {
    try {
      if (typeof window !== "undefined" && window.localStorage) {
        const storedVersion = localStorage.getItem("appVersion");
        if (storedVersion !== currentVersion) {
          localStorage.clear();
          localStorage.setItem("appVersion", currentVersion);
        }
      }
    } catch (error) {
      console.warn("Error accessing localStorage for version check:", error);
    }
  }, [currentVersion]);

  return (
    <>
      {useScrollToTop()}
      <ErrorBoundary>
        <CacheProvider value={emotionCache}>
          <QueryClientProvider client={queryClient}>
            <ReduxProvider store={store}>
              <VendorAuthProvider>
                <SettingsProvider>
                  <SettingsConsumer>
                    {(value) => (
                      <ThemeProvider
                        theme={createTheme({
                          direction: value?.settings?.direction,
                          responsiveFontSizes: value?.settings?.responsiveFontSizes,
                          mode: value?.settings?.theme,
                        })}
                      >
                        <RTL direction={value?.settings?.direction}>
                          <CssBaseline />
                          <Toaster position="top-center" />
                          <ErrorBoundary>
                            {getLayout(<Component {...pageProps} />)}
                          </ErrorBoundary>
                        </RTL>
                      </ThemeProvider>
                    )}
                  </SettingsConsumer>
                </SettingsProvider>
              </VendorAuthProvider>
            </ReduxProvider>
            <ReactQueryDevtools initialIsOpen={false} position="bottom-right" />
          </QueryClientProvider>
        </CacheProvider>
      </ErrorBoundary>
    </>
  );
}

export default MyApp;
export { getServerSideProps };

