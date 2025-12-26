import React from "react";
import { Fade, Popover } from "@mui/material";
import Menu from "./Menu";

const AccountPopover = (props) => {
  const { cartListRefetch, anchorEl, onClose, open, ...other } = props;

  // Check if anchor element exists and is mounted in DOM
  // بررسی اینکه آیا المان anchor وجود دارد و در DOM mount شده است
  const isAnchorElValid = (el) => {
    if (!el) return false;
    try {
      // Check if element is connected to DOM
      // بررسی اینکه آیا المان به DOM متصل است
      if (typeof el.isConnected !== "undefined") {
        return el.isConnected !== false;
      }
      // Fallback: check if element is in document
      // Fallback: بررسی اینکه آیا المان در document است
      return document.body.contains(el);
    } catch (error) {
      // Silently handle error - don't render error object
      // مدیریت خاموش خطا - object خطا را render نکن
      return false;
    }
  };

  // Only render Popover if anchorEl is valid
  // فقط رندر Popover اگر anchorEl معتبر باشد
  const isValidAnchor = anchorEl && isAnchorElValid(anchorEl);
  const shouldOpen = open && isValidAnchor;

  return (
    <>
      {isValidAnchor && (
        <Popover
          disableScrollLock={true}
          anchorEl={anchorEl}
          anchorOrigin={{
            vertical: "bottom",
            horizontal: "center",
          }}
          keepMounted
          onClose={onClose}
          open={shouldOpen}
          PaperProps={{ sx: { width: 300 } }}
          transitionDuration={3}
          TransitionComponent={Fade}
          TransitionProps={{
            timeout: 300,
          }}
          // Remove onError - MUI Popover doesn't support it
          // حذف onError - MUI Popover از آن پشتیبانی نمی‌کند
          {...other}
        >
          <Menu onClose={onClose} cartListRefetch={cartListRefetch} />
        </Popover>
      )}
    </>
  );
};

AccountPopover.propTypes = {};

export default AccountPopover;

