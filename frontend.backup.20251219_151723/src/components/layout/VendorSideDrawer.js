import React, { useState, useEffect } from "react";
import PropTypes from "prop-types";
import { useTheme } from "@emotion/react";
import { Grid, IconButton, Button, Divider, Stack } from "@mui/material";
import CustomSideDrawer from "../side-drawer/CustomSideDrawer";
import { VendorMenuBar } from "./VendorLayout";
import MenuIcon from "@mui/icons-material/Menu";
import SwapHorizIcon from "@mui/icons-material/SwapHoriz";
import { ModuleSelection } from "../landing-page/hero-section/module-selection";
import { useSelector } from "react-redux";
import { useTranslation } from "react-i18next";

const VendorSideDrawer = ({ t }) => {
  const theme = useTheme();
  const { t: translate } = useTranslation();
  const [sideDrawerOpen, setSideDrawerOpen] = useState(false);
  const [openModuleSelection, setOpenModuleSelection] = useState(false);
  const { selectedModule } = useSelector((state) => state.utilsData);
  
  // Ensure zone ID is set from current module when component mounts
  useEffect(() => {
    if (typeof window !== "undefined") {
      const currentZoneId = localStorage.getItem("zoneid");
      const currentModule = JSON.parse(localStorage.getItem("module") || "null");
      
      // If zone ID is missing but we have a module with zones, set it
      if (!currentZoneId && currentModule?.zones?.length > 0) {
        const zoneIds = currentModule.zones.map((zone) => zone.id).filter(Boolean);
        if (zoneIds.length > 0) {
          localStorage.setItem("zoneid", JSON.stringify(zoneIds));
        }
      }
      // Also check selectedModule from Redux
      else if (!currentZoneId && selectedModule?.zones?.length > 0) {
        const zoneIds = selectedModule.zones.map((zone) => zone.id).filter(Boolean);
        if (zoneIds.length > 0) {
          localStorage.setItem("zoneid", JSON.stringify(zoneIds));
        }
      }
    }
  }, [selectedModule]);

  const handleModuleSelectionClose = (selectedItem) => {
    setOpenModuleSelection(false);
    setSideDrawerOpen(false);
    if (selectedItem) {
      // Module selection will handle zone ID persistence
      // If switching to beauty module, stay in vendor dashboard
      if (selectedItem?.module_type === "beauty") {
        // Reload to apply new zone ID
        window.location.reload();
      } else {
        // Navigate to home for other modules
        window.location.href = "/home";
      }
    }
  };

  const handleOpenModuleSelection = () => {
    setOpenModuleSelection(true);
  };

  return (
    <>
      <Grid item xs={2}>
        <IconButton variant="outlined" onClick={() => setSideDrawerOpen(true)}>
          <MenuIcon
            sx={{
              color: (theme) => theme.palette.primary.main,
            }}
          />
        </IconButton>
      </Grid>
      <CustomSideDrawer
        open={sideDrawerOpen}
        onClose={() => setSideDrawerOpen(false)}
        anchor="left"
      >
        <Stack spacing={2} sx={{ pt: 2 }}>
          <Button
            variant="outlined"
            startIcon={<SwapHorizIcon />}
            onClick={handleOpenModuleSelection}
            sx={{ mx: 2 }}
          >
            {t ? t("Switch Module") : translate("Switch Module")}
          </Button>
          <Divider />
        <VendorMenuBar t={t} />
        </Stack>
      </CustomSideDrawer>
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
    </>
  );
};

VendorSideDrawer.propTypes = {};

export default VendorSideDrawer;

