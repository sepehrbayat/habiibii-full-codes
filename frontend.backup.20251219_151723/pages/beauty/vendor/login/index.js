import React, { useContext, useEffect } from "react";
import CssBaseline from "@mui/material/CssBaseline";
import {
  Box,
  Button,
  Paper,
  Stack,
  TextField,
  Typography,
} from "@mui/material";
import { useFormik } from "formik";
import * as Yup from "yup";
import { useMutation } from "react-query";
import { useRouter } from "next/router";
import toast from "react-hot-toast";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import { BeautyVendorApi } from "../../../../src/api-manage/another-formated-api/beautyVendorApi";
import { onSingleErrorResponse } from "../../../../src/api-manage/api-error-response/ErrorResponses";
import {
  VendorAuthContext,
  getVendorToken,
} from "../../../../src/contexts/vendor-auth-context";
import { getServerSideProps } from "../../../index";

const validationSchema = Yup.object({
  identifier: Yup.string().required("Email or phone is required"),
  password: Yup.string().required("Password is required"),
});

const VendorLoginPage = ({ configData, landingPageData }) => {
  const router = useRouter();
  const { login } = useContext(VendorAuthContext);
  const from = router?.query?.from;

  const { mutateAsync, isLoading } = useMutation(
    (payload) => BeautyVendorApi.loginVendor(payload),
    {
      onError: onSingleErrorResponse,
    }
  );

  useEffect(() => {
    const token = getVendorToken();
    if (token) {
      router.replace("/beauty/vendor/dashboard");
    }
  }, [router]);

  const formik = useFormik({
    initialValues: {
      identifier: "",
      password: "",
    },
    validationSchema,
    onSubmit: async (values) => {
      try {
        const isEmail = /\S+@\S+\.\S+/.test(values.identifier);
        const payload = {
          password: values.password,
          ...(isEmail
            ? { email: values.identifier }
            : { phone: values.identifier }),
        };

        const response = await mutateAsync(payload);
        const token =
          response?.data?.token ||
          response?.data?.access_token ||
          response?.data?.data?.token;
        const vendorInfo =
          response?.data?.seller ||
          response?.data?.vendor ||
          response?.data?.data ||
          null;

        if (token) {
          login(token, vendorInfo);
          toast.success("Login successful");
          router.replace(from || "/beauty/vendor/dashboard");
        } else {
          toast.error("Unable to login. Please try again.");
        }
      } catch (e) {
        // onSingleErrorResponse handles toast rendering
      }
    },
  });

  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Vendor Login` : "Loading..."}
        image={`${getImageUrl(
          { value: configData?.logo_storage },
          "business_logo_url",
          configData
        )}/${configData?.fav_icon}`}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <Box
          display="flex"
          alignItems="center"
          justifyContent="center"
          sx={{ minHeight: "70vh", px: 2 }}
        >
          <Paper elevation={3} sx={{ maxWidth: 480, width: "100%", p: 4 }}>
            <Stack spacing={3}>
              <Box>
                <Typography variant="h4" fontWeight="bold" gutterBottom>
                  Vendor Login
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Sign in with your email or phone number and password to access
                  the vendor dashboard.
                </Typography>
              </Box>
              <form onSubmit={formik.handleSubmit} noValidate>
                <Stack spacing={2.5}>
                  <TextField
                    fullWidth
                    id="identifier"
                    name="identifier"
                    label="Email or Phone"
                    value={formik.values.identifier}
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    error={
                      formik.touched.identifier && Boolean(formik.errors.identifier)
                    }
                    helperText={
                      formik.touched.identifier && formik.errors.identifier
                    }
                  />
                  <TextField
                    fullWidth
                    id="password"
                    name="password"
                    label="Password"
                    type="password"
                    value={formik.values.password}
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    error={
                      formik.touched.password && Boolean(formik.errors.password)
                    }
                    helperText={formik.touched.password && formik.errors.password}
                  />
                  <Button
                    variant="contained"
                    type="submit"
                    fullWidth
                    size="large"
                    disabled={isLoading}
                  >
                    {isLoading ? "Signing in..." : "Sign in"}
                  </Button>
                </Stack>
              </form>
            </Stack>
          </Paper>
        </Box>
      </MainLayout>
    </>
  );
};

export default VendorLoginPage;
export { getServerSideProps };


