import React from "react";
import { useRouter } from "next/router";
import {
  AppBar,
  Box,
  IconButton,
  Stack,
  Toolbar,
  Typography,
  Button,
} from "@mui/material";
import ArrowBackIosNewIcon from "@mui/icons-material/ArrowBackIosNew";

const VendorPageHeader = ({
  title,
  subtitle,
  onBack,
  nextLabel,
  onNext,
  rightContent,
  fallbackHref = "/beauty/vendor/dashboard",
  sticky = true,
}) => {
  const router = useRouter();

  const handleBack = () => {
    if (onBack) return onBack();
    if (typeof window !== "undefined" && window.history.length > 1) {
      router.back();
    } else {
      router.push(fallbackHref);
    }
  };

  const handleNext = () => {
    if (onNext) onNext();
  };

  return (
    <AppBar
      position={sticky ? "sticky" : "static"}
      color="default"
      elevation={0}
      sx={(theme) => ({
        top: 0,
        backgroundColor: theme.palette.background.paper,
        borderBottom: `1px solid ${theme.palette.divider}`,
        zIndex: theme.zIndex.appBar + 1,
      })}
    >
      <Toolbar
        sx={{
          minHeight: 64,
          px: { xs: 1, sm: 2 },
        }}
      >
        <Stack direction="row" alignItems="center" spacing={1} flex={1}>
          <IconButton
            size="small"
            edge="start"
            aria-label="back"
            onClick={handleBack}
          >
            <ArrowBackIosNewIcon fontSize="small" />
          </IconButton>
          <Box>
            <Typography variant="h6" fontWeight={700}>
              {title}
            </Typography>
            {subtitle && (
              <Typography variant="body2" color="text.secondary">
                {subtitle}
              </Typography>
            )}
          </Box>
        </Stack>

        <Stack direction="row" spacing={1} alignItems="center">
          {rightContent}
          {nextLabel && (
            <Button size="small" variant="contained" onClick={handleNext}>
              {nextLabel}
            </Button>
          )}
        </Stack>
      </Toolbar>
    </AppBar>
  );
};

export default VendorPageHeader;

