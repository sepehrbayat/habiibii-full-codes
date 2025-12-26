import React, { useContext } from "react";
import { Grid, useMediaQuery, List, ListItem, ListItemIcon, ListItemText, MenuItem, Typography, Divider } from "@mui/material";
import { useRouter } from "next/router";
import { useTheme } from "@emotion/react";
import Link from "next/link";
import { styled } from "@mui/material/styles";
import DashboardIcon from "@mui/icons-material/Dashboard";
import BookOnlineIcon from "@mui/icons-material/BookOnline";
import PeopleIcon from "@mui/icons-material/People";
import BusinessCenterIcon from "@mui/icons-material/BusinessCenter";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import StoreIcon from "@mui/icons-material/Store";
import CardMembershipIcon from "@mui/icons-material/CardMembership";
import AccountBalanceIcon from "@mui/icons-material/AccountBalance";
import AccountCircleIcon from "@mui/icons-material/AccountCircle";
import InventoryIcon from "@mui/icons-material/Inventory";
import CardGiftcardIcon from "@mui/icons-material/CardGiftcard";
import LoyaltyIcon from "@mui/icons-material/Loyalty";
import VendorSideDrawer from "./VendorSideDrawer";
import LogoutIcon from "@mui/icons-material/Logout";
import { VendorAuthContext } from "../../contexts/vendor-auth-context";
import VendorPageHeader from "../navigation/VendorPageHeader";

const CustomBodyContent = styled("div")(({ theme }) => ({
  flexGrow: 1,
  padding: theme.spacing(3),
}));

const vendorMenuData = [
  { id: 1, name: "Dashboard", path: "/beauty/vendor/dashboard", icon: <DashboardIcon /> },
  { id: 2, name: "Bookings", path: "/beauty/vendor/bookings", icon: <BookOnlineIcon /> },
  { id: 3, name: "Staff", path: "/beauty/vendor/staff", icon: <PeopleIcon /> },
  { id: 4, name: "Services", path: "/beauty/vendor/services", icon: <BusinessCenterIcon /> },
  { id: 5, name: "Calendar", path: "/beauty/vendor/calendar", icon: <CalendarTodayIcon /> },
  { id: 6, name: "Retail", path: "/beauty/vendor/retail/products", icon: <StoreIcon /> },
  { id: 7, name: "Subscription", path: "/beauty/vendor/subscription", icon: <CardMembershipIcon /> },
  { id: 8, name: "Finance", path: "/beauty/vendor/finance", icon: <AccountBalanceIcon /> },
  { id: 9, name: "Profile", path: "/beauty/vendor/profile", icon: <AccountCircleIcon /> },
  { id: 10, name: "Packages", path: "/beauty/vendor/packages", icon: <InventoryIcon /> },
  { id: 11, name: "Gift Cards", path: "/beauty/vendor/gift-cards", icon: <CardGiftcardIcon /> },
  { id: 12, name: "Loyalty", path: "/beauty/vendor/loyalty", icon: <LoyaltyIcon /> },
  { id: 13, name: "Logout", icon: <LogoutIcon />, action: "logout" },
];

export const VendorMenuBar = ({ t }) => {
  const router = useRouter();
  const { logout } = useContext(VendorAuthContext);

  const activeRoute = (routeName, currentRoute) => {
    return currentRoute.startsWith(routeName.toLowerCase());
  };

  const handleLogout = () => {
    logout();
    router.replace("/beauty/vendor/login");
  };

  return (
    <List>
      <Typography
        sx={{
          padding: "0px 0px 30px 0px",
          color: (theme) => theme.palette.neutral[1000],
          fontSize: "26px",
          fontWeight: "700",
          textAlign: "center",
          marginTop: "12px",
        }}
      >
        {t ? t("Vendor Dashboard") : "Vendor Dashboard"}
      </Typography>
      {vendorMenuData.map((item, index) => {
        const isLogout = item.action === "logout";
        const menuContent = (
          <Grid container md={12} xs={12}>
            <Grid md={12} xs={12}>
              <MenuItem
                selected={!isLogout && activeRoute(item?.path, router.pathname)}
                onClick={isLogout ? handleLogout : undefined}
              >
                <ListItem>
                  <ListItemIcon>
                    {item?.icon}
                  </ListItemIcon>
                  <ListItemText primary={t ? t(item?.name) : item?.name} />
                </ListItem>
              </MenuItem>
              <Divider />
            </Grid>
          </Grid>
        );

        if (isLogout) {
          return <React.Fragment key={item.id}>{menuContent}</React.Fragment>;
        }

        return (
          <Link href={`${item?.path}`} key={item.id}>
            {menuContent}
          </Link>
        );
      })}
    </List>
  );
};

const deriveTitleFromPath = (pathname) => {
  const match = vendorMenuData.find(
    (item) => item.path && pathname.startsWith(item.path)
  );
  if (match?.name) return match.name;
  const segments = pathname.split("/").filter(Boolean);
  const last = segments[segments.length - 1] || "Dashboard";
  return last
    .replace(/[\[\]]/g, "")
    .replace(/[-_]/g, " ")
    .replace(/\b\w/g, (c) => c.toUpperCase());
};

const VendorLayout = (props) => {
  const { children, configData, t, pageTitle } = props;
  const router = useRouter();
  const theme = useTheme();
  const isSmall = useMediaQuery(theme.breakpoints.down("sm"));
  const derivedTitle = pageTitle || deriveTitleFromPath(router.pathname);

  return (
    <>
      <Grid
        container
        md={12}
        spacing={{ xs: 0, md: 3, lg: 2 }}
        sx={{ minHeight: "100vh" }}
      >
        <Grid
          container
          item
          mt="1rem"
          sx={{ display: { sm: "block", md: "none" } }}
          alignItems="center"
        >
          <VendorSideDrawer t={t} />
        </Grid>
        <Grid
          item
          lg={2.5}
          md={3}
          xs={12}
          sx={{ display: { xs: "none", md: "block" } }}
        >
          <VendorMenuBar configData={configData} t={t} />
        </Grid>

        <Grid item md={9} lg={9.5} xs={12}>
          <VendorPageHeader
            title={t ? t(derivedTitle) : derivedTitle}
            fallbackHref="/beauty/vendor/dashboard"
          />
          <CustomBodyContent>{children}</CustomBodyContent>
        </Grid>
      </Grid>
    </>
  );
};

VendorLayout.propTypes = {};

export default VendorLayout;

