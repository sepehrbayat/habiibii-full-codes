import React, { useState, useEffect } from "react";
import dynamic from "next/dynamic";
import Head from "next/head";
import { Box } from "@mui/material";
import { useRouter } from "next/router";

const AuthModal = dynamic(() => import("components/auth/AuthModal"), {
  ssr: false,
});

const LoginPage = () => {
  const router = useRouter();
  const [modalFor, setModalFor] = useState("sign-in");

  useEffect(() => {
    setModalFor("sign-in");
  }, []);

  useEffect(() => {
    if (!router.isReady) return;
    const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;
    if (token) {
      router.replace("/module-select");
    }
  }, [router.isReady, router]);

  const handleClose = () => {
    router.push("/");
  };

  return (
    <>
      <Head>
        <title>Sign In</title>
      </Head>
      <Box
        sx={{
          minHeight: "100vh",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          bgcolor: (theme) => theme.palette.background.default,
          p: 2,
        }}
      >
        <AuthModal
          modalFor={modalFor}
          setModalFor={setModalFor}
          open
          handleClose={handleClose}
        />
      </Box>
    </>
  );
};

export default LoginPage;

