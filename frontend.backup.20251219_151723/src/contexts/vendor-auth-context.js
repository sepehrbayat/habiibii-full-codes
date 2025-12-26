import { createContext, useEffect, useState } from "react";
import PropTypes from "prop-types";

const initialVendorAuth = {
  isAuthenticated: false,
  vendorToken: null,
  vendorInfo: null,
};

export const getVendorToken = () => {
  if (typeof window !== "undefined") {
    return window.localStorage.getItem("vendor_token");
  }
  return null;
};

export const setVendorToken = (token) => {
  if (typeof window !== "undefined") {
    if (token) {
      window.localStorage.setItem("vendor_token", token);
    } else {
      window.localStorage.removeItem("vendor_token");
    }
  }
};

export const getVendorInfo = () => {
  if (typeof window !== "undefined") {
    const vendorInfo = window.localStorage.getItem("vendor_info");
    return vendorInfo ? JSON.parse(vendorInfo) : null;
  }
  return null;
};

export const setVendorInfo = (info) => {
  if (typeof window !== "undefined") {
    if (info) {
      window.localStorage.setItem("vendor_info", JSON.stringify(info));
    } else {
      window.localStorage.removeItem("vendor_info");
    }
  }
};

export const VendorAuthContext = createContext({
  vendorAuth: initialVendorAuth,
  login: () => {},
  logout: () => {},
  updateVendorInfo: () => {},
});

export const VendorAuthProvider = (props) => {
  const { children } = props;
  const [vendorAuth, setVendorAuth] = useState(initialVendorAuth);

  useEffect(() => {
    const token = getVendorToken();
    const info = getVendorInfo();
    if (token) {
      setVendorAuth({
        isAuthenticated: true,
        vendorToken: token,
        vendorInfo: info,
      });
    }
  }, []);

  const login = (token, vendorInfo = null) => {
    setVendorToken(token);
    setVendorInfo(vendorInfo || null);
    setVendorAuth({
      isAuthenticated: !!token,
      vendorToken: token,
      vendorInfo: vendorInfo || null,
    });
  };

  const logout = () => {
    setVendorToken(null);
    setVendorInfo(null);
    setVendorAuth({ ...initialVendorAuth });
  };

  const updateVendorInfo = (vendorInfo) => {
    setVendorInfo(vendorInfo);
    setVendorAuth((prev) => ({
      ...prev,
      vendorInfo: vendorInfo,
    }));
  };

  return (
    <VendorAuthContext.Provider
      value={{
        vendorAuth,
        login,
        logout,
        updateVendorInfo,
      }}
    >
      {children}
    </VendorAuthContext.Provider>
  );
};

VendorAuthProvider.propTypes = {
  children: PropTypes.node.isRequired,
};

export const VendorAuthConsumer = VendorAuthContext.Consumer;

