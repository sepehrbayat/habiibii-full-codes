import toast from "react-hot-toast";
import { t } from "i18next";
import Router from "next/router";
import { handleBeautyError } from "../../helper-functions/beautyErrorHandler";

export const handleTokenExpire = (item, status) => {
  if (status === 401) {
    if (window.localStorage.getItem("token")) {
      toast.error(t("Your account is inactive or Your token has been expired"));
      window?.localStorage.removeItem("token");
      Router.push("/home", undefined, { shallow: true });
    }
  } else {
    toast.error(item?.message, {
      id: "error",
    });
  }
};

export const onErrorResponse = (error) => {
  error?.response?.data?.errors?.forEach((item) => {
    handleTokenExpire(item);
  });
};
export const onSingleErrorResponse = (error) => {
  // For 401, avoid double-toast: rely on token-expire handler (which also redirects) and skip beauty error toasts.
  if (error?.response?.status === 401) {
    handleTokenExpire(error, error?.response?.status);
    return;
  }

  const parsedErrors = handleBeautyError(error);
  parsedErrors.forEach((err, index) => {
    // Use unique ids so multiple errors are all shown; keep a stable id when only one
    const toastId = parsedErrors.length === 1 ? "error" : `error-${index}`;
    toast.error(err.message, { id: toastId });
  });
};
