import { getVendorToken as getVendorTokenFromContext } from "../contexts/vendor-auth-context";

export const getToken = () => {
  if (typeof window !== "undefined") {
    return window.localStorage.getItem("token");
  }
};

// Re-export the context-aware implementation to keep a single source of truth
export const getVendorToken = getVendorTokenFromContext;

export const getGuestId = () => {
  if (typeof window !== "undefined") {
    return window.localStorage.getItem("guest_id");
  }
};
