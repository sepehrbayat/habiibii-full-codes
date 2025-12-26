import React, { useEffect, useState } from "react";
import List from "@mui/material/List";
import ListItemButton from "@mui/material/ListItemButton";
import ListItemText from "@mui/material/ListItemText";
import { alpha } from "@mui/material";
import Box from "@mui/material/Box";
import { t } from "i18next";
import { useRouter } from "next/router";
import CollapsableMenu from "./CollapsableMenu";
import useGetLatestStore from "../../../../api-manage/hooks/react-query/store/useGetLatestStore";
import { useGetCategories } from "api-manage/hooks/react-query/all-category/all-categorys";
import useGetPopularStore from "../../../../api-manage/hooks/react-query/store/useGetPopularStore";
import { useDispatch, useSelector } from "react-redux";
import { Scrollbar } from "../../../srollbar";
import ButtonsContainer from "./ButtonsContainer";
import { getStoresOrRestaurants } from "helper-functions/getStoresOrRestaurants";
import { getModuleId } from "helper-functions/getModuleId";
import { setPopularStores } from "redux/slices/storedData";
import ThemeSwitches from "../ThemeSwitches";
import CustomLanguage from "../language/CustomLanguage";
import { getModule } from "helper-functions/getLanguage";
import { getCurrentModuleType } from "helper-functions/getCurrentModuleType";
import JoinUsDropdown from "./JoinUsDropdown";
import SwapHorizIcon from "@mui/icons-material/SwapHoriz";
import { ModuleSelection } from "../../../landing-page/hero-section/module-selection";
import { ModuleTypes } from "helper-functions/moduleTypes";

const MobileTopMenu = ({
  handleRoute,
  toggleDrawer,
  setOpenDrawer,
  handleLogout,
  openModal,
  isLogoutLoading,
  setOpenModal,
}) => {
  const { wishLists } = useSelector((state) => state.wishList);
  const { selectedModule: reduxSelectedModule } = useSelector((state) => state.utilsData);
  const router = useRouter();
  const [openModuleSelection, setOpenModuleSelection] = useState(false);
  let token = undefined;
  let location = undefined;
  let storedModule = null;
  if (typeof window !== undefined) {
    location = localStorage.getItem("location");
    token = localStorage.getItem("token");
    storedModule = JSON.parse(localStorage.getItem("module") || "null");
  }
  const { countryCode, language } = useSelector((state) => state.configData);
  
  // Get selected module from Redux or localStorage
  const selectedModule = reduxSelectedModule || storedModule;
  
  // Ensure zone ID is set from current module when component mounts
  useEffect(() => {
    if (typeof window !== "undefined") {
      const currentZoneId = localStorage.getItem("zoneid");
      const currentModule = storedModule || reduxSelectedModule;
      
      // If zone ID is missing but we have a module with zones, set it
      if (!currentZoneId && currentModule?.zones?.length > 0) {
        const zoneIds = currentModule.zones.map((zone) => zone.id).filter(Boolean);
        if (zoneIds.length > 0) {
          localStorage.setItem("zoneid", JSON.stringify(zoneIds));
        }
      }
    }
  }, [reduxSelectedModule, storedModule]);

  const handleModuleSelectionClose = (selectedItem) => {
    setOpenModuleSelection(false);
    setOpenDrawer(false);
    if (selectedItem) {
      // Module selection will handle zone ID persistence
      // If switching to beauty module, navigate to beauty home
      if (selectedItem?.module_type === ModuleTypes.BEAUTY) {
        window.location.href = "/beauty";
      } else {
        // Navigate to home for other modules
        window.location.href = "/home";
      }
    }
  };

  const handleOpenModuleSelection = () => {
    setOpenModuleSelection(true);
  };
  const rentalCategories = useSelector(
    (state) => state?.rentalCategoriesLists?.rentalCategories
  );

  const { data: categoriesData, refetch } = useGetCategories();
  const { data: latestStore, refetch: refetchStore } = useGetLatestStore();
  const type = "all";
  const pageLimit = 12;
  const {
    data,
    refetch: popularRefetch,
    isFetching,
  } = useGetPopularStore({
    type,
    offset: 1,
    limit: pageLimit,
  });
  const { popularStores } = useSelector((state) => state.storedData);
  const dispatch = useDispatch();
  useEffect(() => {
    if (popularStores.length === 0 && getModuleId()) {
      popularRefetch();
    }
  }, []);
  useEffect(() => {
    if (
      data &&
      data?.pages?.length > 0 &&
      data?.pages?.[0]?.stores?.length > 0
    ) {
      dispatch(setPopularStores(data?.pages?.[0]?.stores));
    }
  }, [data]);
  useEffect(() => {
    if (getModuleId()) {
      refetch();
      refetchStore();
    }
  }, []);
  const popular = t("Popular");
  const latest = t("Latest");


  const collapsableMenu = {
    cat: {
      text: "Categories",
      items:
        getModule()?.module_type !== "rental"
          ? categoriesData?.data?.map((item) => item)
          : rentalCategories?.map((item) => item),
      path: "/category",
    },
    latest: {
      text: `${latest} ${getStoresOrRestaurants()}`,
      items: latestStore?.stores?.slice(0, 12)?.map((i) => i),
      path: getCurrentModuleType() === "rental" ? "/rental/provider-details" : "/store",
    },
    popularStore: {
      text: `${popular} ${getStoresOrRestaurants()}`,
      items: popularStores?.map((i) => i),
      path: getCurrentModuleType() === "rental" ? "/rental/provider-details" : "/store",
    },
    profile: {
      text: "Profile",
    },
  };
  const getWishlistCount = () => {
    return wishLists?.item?.length + wishLists?.store?.length;
  };
  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        width: "auto",
        height: "90%",
        justifyContent: "space-between",
      }}
      role="presentation"
      onKeyDown={toggleDrawer(false)}
    >
      <Box sx={{ paddingX: "20px" }}>
        <Scrollbar style={{ maxHeight: "80vh" }}>
          <List component="nav" aria-labelledby="nested-list-subheader">
            <>
              <ListItemButton
                sx={{
                  color: (theme) => theme.palette.primary.main,
                  marginTop: "30px",
                  "&:hover": {
                    backgroundColor: (theme) =>
                      alpha(theme.palette.primary.main, 0.3),
                  },
                }}
              >
                <ListItemText
                  sx={{ fontSize: "12px" }}
                  primary={t("Home")}
                  onClick={() => handleRoute("/home")}
                />
              </ListItemButton>
              {location && (
                <>
                  <CollapsableMenu
                    value={collapsableMenu.cat}
                    setOpenDrawer={setOpenDrawer}
                    toggleDrawers={toggleDrawer}
                    pathName="/categories"
                    forcategory="true"
                  />
                  <CollapsableMenu
                    value={collapsableMenu.latest}
                    setOpenDrawer={setOpenDrawer}
                    toggleDrawers={toggleDrawer}
                    pathName="/store/latest"
                  />
                  <CollapsableMenu
                    value={collapsableMenu.popularStore}
                    setOpenDrawer={setOpenDrawer}
                    toggleDrawers={toggleDrawer}
                    pathName="/store/popular"
                  />
                </>
              )}

              <JoinUsDropdown />

              <ListItemButton
                sx={{
                  color: (theme) => theme.palette.primary.main,
                  "&:hover": {
                    backgroundColor: (theme) =>
                      alpha(theme.palette.primary.main, 0.3),
                  },
                }}
                onClick={handleOpenModuleSelection}
              >
                <ListItemText primary={t("Switch Module")} />
                <SwapHorizIcon sx={{ fontSize: "20px", ml: 1 }} />
              </ListItemButton>

              <ListItemButton
                sx={{ color: (theme) => theme.palette.primary.main }}
              >
                <ListItemText>{t("Theme Mode")}</ListItemText>
                <ThemeSwitches noText />
              </ListItemButton>
              <ListItemButton
                sx={{ color: (theme) => theme.palette.primary.main }}
              >
                <ListItemText>{t("Language")}</ListItemText>
                <CustomLanguage
                  countryCode={countryCode}
                  language={language}
                  noText
                  key={countryCode}
                />
              </ListItemButton>
              {/*{token && (*/}
              {/*  <>*/}
              {/*    {router.pathname === "/" && (*/}
              {/*      <ListItemButton*/}
              {/*        sx={{*/}
              {/*          "&:hover": {*/}
              {/*            backgroundColor: (theme) =>*/}
              {/*              alpha(theme.palette.primary.main, 0.3),*/}
              {/*          },*/}
              {/*        }}*/}
              {/*      >*/}
              {/*        <ListItemText*/}
              {/*          primary={t("Favorites")}*/}
              {/*          onClick={() => handleRoute("wishlist")}*/}
              {/*        />*/}
              {/*        <CustomChip*/}
              {/*          label={getWishlistCount()}*/}
              {/*          color="secondary"*/}
              {/*        />*/}
              {/*      </ListItemButton>*/}
              {/*    )}*/}
              {/*  </>*/}
              {/*)}*/}
            </>
          </List>
        </Scrollbar>
      </Box>
      <ButtonsContainer
        token={token}
        handleRoute={handleRoute}
        handleLogout={handleLogout}
        openModal={openModal}
        isLogoutLoading={isLogoutLoading}
        setOpenModal={setOpenModal}
      />
      <ModuleSelection
        location={openModuleSelection ? true : null}
        closeModal={handleModuleSelectionClose}
        isSelected={selectedModule}
        disableAutoFocus={false}
        setOpenModuleSelection={(value) => {
          if (typeof value === 'boolean') {
            setOpenModuleSelection(value);
          }
        }}
        zoneId={typeof window !== "undefined" ? localStorage.getItem("zoneid") : null}
      />
    </Box>
  );
};

export default MobileTopMenu;
