import React, { useState, useEffect } from "react";
import { Stack, Menu, MenuItem, Button } from "@mui/material";
import Link from "next/link";
import { NavLinkStyle } from "../NavBar.style";
import MonetizationOnIcon from "@mui/icons-material/MonetizationOn";
import dynamic from "next/dynamic";
import { useRouter } from "next/router";
import { useDispatch } from "react-redux";
import { setSelectedModule } from "redux/slices/utils";

const NavLinks = ({ zoneid, t, moduleType }) => {
  const [openCategoryModal, setCategoryModal] = useState(false);
  const [openRestaurantModal, setRestaurantModal] = useState(false);
  const [anchorEl, setAnchorEl] = useState(null);
  const [isLoggedIn, setIsLoggedIn] = useState(null)
  const router = useRouter();
  const dispatch = useDispatch();
  const NavStore = dynamic(() => import("./NavStore"), {
    ssr: false,
  });
  const NavCategory = dynamic(() => import("./NavCategory"), {
    ssr: false,
  });

  useEffect(() => {
    const token = localStorage.getItem("token");
    setIsLoggedIn(Boolean(token))
  }, [])

  const isUserLoggedIn = () => {
    return Boolean(localStorage.getItem("token"));
  };

  const handleMenuOpen = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = () => {
    setAnchorEl(null);
  }

  const handleBackToModuleSelect = () => {
    localStorage.removeItem("module");
    dispatch(setSelectedModule(null));
    router.push("/module-select");
  };

  return (
    <Stack
      direction="row"
      alignItems="center"
      spacing={2}
      sx={{ paddingRight: "20px" }}
    >
      {zoneid && (
        <>
          <Link href={moduleType === "beauty" ? "/beauty" : "/home"}>
            <NavLinkStyle
              underline="none"
              // language_direction={language_direction}
              sx={{ cursor: "pointer", fontWeight: "bold" }}
            >
              {moduleType === "beauty" ? t("Beauty Home") : t("Home")}
            </NavLinkStyle>
          </Link>
          {moduleType === "beauty" ? (
            <>
              <Link href="/beauty/salons">
                <NavLinkStyle
                  underline="none"
                  sx={{ cursor: "pointer" }}
                >
                  {t("Beauty Salons")}
                </NavLinkStyle>
              </Link>
              <Link href="/beauty/packages">
                <NavLinkStyle
                  underline="none"
                  sx={{ cursor: "pointer" }}
                >
                  {t("Packages")}
                </NavLinkStyle>
              </Link>
              <Button
                size="small"
                variant="outlined"
                onClick={handleBackToModuleSelect}
                sx={{ textTransform: "none" }}
              >
                {t("Change module")}
              </Button>
            </>
          ) : moduleType !== "parcel" ? (
            <>
              <NavCategory
                openModal={openCategoryModal}
                setModal={setCategoryModal}
                setRestaurantModal={setRestaurantModal}
              />
              <NavStore
                openModal={openRestaurantModal}
                setModal={setRestaurantModal}
              />
            </>
          ) : (
            <Link href="/help-and-support">
              <NavLinkStyle
                underline="none"
                // language_direction={language_direction}
                sx={{ cursor: "pointer" }}
              >
                {t("Contact")}
              </NavLinkStyle>
            </Link>
          )}
          {/* {isLoggedIn && (
            <>
              <Button 
                onClick={handleMenuOpen} 
                variant="outlined" 
                sx={{ 
                  textTransform: "none", 
                  borderColor: "primary.main", 
                  color: "primary.main",
                  padding: "6px 12px",
                  borderRadius: "8px",
                  fontWeight: "bold",
                  display: "flex",
                  alignItems: "center",
                  gap: "8px",
                }}
              >
                <MonetizationOnIcon sx={{ fontSize: 20 }} />
                {t("Become a seller | rider")}
              </Button>
              <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={handleMenuClose}>
                <MenuItem onClick={handleMenuClose}
                  style={{
                    borderBottom: "1px solid #E0E0E0",
                    marginBottom: "8px",
                    paddingBottom: "8px",
                  }}
                >
                  <Link href={`${process.env.NEXT_PUBLIC_BASE_URL}/login/seller`} passHref>
                    <NavLinkStyle underline="none">{t("Seller login")}</NavLinkStyle>
                  </Link>
                </MenuItem>
                <MenuItem onClick={handleMenuClose}>
                  <Link href="/store-registration?active=active" passHref>
                    <NavLinkStyle underline="none">{t("Become a seller")}</NavLinkStyle>
                  </Link>
                </MenuItem>
                <MenuItem onClick={handleMenuClose}>
                  <Link href="/rider-registration" passHref>
                    <NavLinkStyle underline="none">{t("Become a rider")}</NavLinkStyle>
                  </Link>
                </MenuItem>
              </Menu>
            </>
          )} */}
        </>
      )}
    </Stack>
  );
};

NavLinks.propTypes = {};

export default React.memo(NavLinks);
