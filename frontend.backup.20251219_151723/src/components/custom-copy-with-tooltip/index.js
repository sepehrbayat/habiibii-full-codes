import React, { useState } from "react";
import PropTypes from "prop-types";
import IconButton from "@mui/material/IconButton";
import ContentCopyIcon from "@mui/icons-material/ContentCopy";
import { Button, Tooltip } from "@mui/material";
import toast from "react-hot-toast";
import CopyCodeIcon from "../referral-code/svg/CopyCodeIcon";

// Fallback copy method for browsers without clipboard API
// روش جایگزین برای مرورگرهایی که از Clipboard API پشتیبانی نمی‌کنند
const fallbackCopyTextToClipboard = (text) => {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.position = "fixed";
  textArea.style.left = "-999999px";
  textArea.style.top = "-999999px";
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  try {
    const successful = document.execCommand('copy');
    if (!successful) {
      console.warn("Fallback copy method failed");
    }
  } catch (err) {
    console.warn("Fallback copy method error:", err);
  }
  document.body.removeChild(textArea);
};

const CustomCopyWithTooltip = (props) => {
  const { t, value, forModal, companyName, referralCode } = props;
  const [copy, setCopy] = useState(false);
  
  const copyReferCode = async (text) => {
    if (typeof window !== undefined) {
      // Check if clipboard API is available
      // بررسی اینکه آیا Clipboard API در دسترس است
      if (navigator.clipboard && navigator.clipboard.writeText) {
        try {
          await window.navigator.clipboard.writeText(text);
        } catch (error) {
          console.warn("Clipboard API failed, using fallback:", error);
          // Fallback: use document.execCommand
          // استفاده از روش جایگزین
          fallbackCopyTextToClipboard(text);
        }
      } else {
        // Fallback: use document.execCommand
        // استفاده از روش جایگزین
        fallbackCopyTextToClipboard(text);
      }
    }
  };
  
  const handleCopy = (coupon_code) => {
    // Check if clipboard API is available
    // بررسی اینکه آیا Clipboard API در دسترس است
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(coupon_code)
        .then(() => {
          setCopy(true);
          toast(() => (
            <span>
              {t("Code")}
              <b style={{ marginLeft: "4px", marginRight: "4px" }}>
                {referralCode}
              </b>
              {t("has been copied")}
            </span>
          ));
        })
        .catch((error) => {
          console.error("Failed to copy code:", error);
          // Try fallback method
          // تلاش برای استفاده از روش جایگزین
          try {
            fallbackCopyTextToClipboard(coupon_code);
            setCopy(true);
            toast(() => (
              <span>
                {t("Code")}
                <b style={{ marginLeft: "4px", marginRight: "4px" }}>
                  {referralCode}
                </b>
                {t("has been copied")}
              </span>
            ));
          } catch (fallbackError) {
            console.error("Fallback copy also failed:", fallbackError);
          }
        });
    } else {
      // Use fallback method
      // استفاده از روش جایگزین
      try {
        fallbackCopyTextToClipboard(coupon_code);
        setCopy(true);
        toast(() => (
          <span>
            {t("Code")}
            <b style={{ marginLeft: "4px", marginRight: "4px" }}>
              {referralCode}
            </b>
            {t("has been copied")}
          </span>
        ));
      } catch (error) {
        console.error("Failed to copy code:", error);
      }
    }
  };
  
  return (
    <Tooltip arrow placement="top" title={copy ? t("Copied") : t("Copy")}>
      {forModal ? (
        <Button
          variant="contained"
          onMouseEnter={() => copy && setCopy(false)}
          onClick={() => handleCopy(value)}
        >
          Copy
        </Button>
      ) : (
        <IconButton
          onMouseEnter={() => copy && setCopy(false)}
          onClick={() => handleCopy(value)}
          sx={{ p: { xs: "0px", sm: "5px" }, m: { xs: "0px", sm: "5px" } }}
        >
          <CopyCodeIcon />
        </IconButton>
      )}
    </Tooltip>
  );
};

CustomCopyWithTooltip.propTypes = {
  t: PropTypes.func.isRequired,
  value: PropTypes.any.isRequired,
};

export default CustomCopyWithTooltip;
