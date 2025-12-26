import React, { useState } from "react";
import List from "@mui/material/List";
import ListItemButton from "@mui/material/ListItemButton";
import ListItemText from "@mui/material/ListItemText";
import Collapse from "@mui/material/Collapse";
import ExpandLess from "@mui/icons-material/ExpandLess";
import ExpandMore from "@mui/icons-material/ExpandMore";
import { useRouter } from "next/router";
import { t } from "i18next";
import { useTheme } from "@mui/material/styles";

const JoinUsDropdown = () => {
  const [open, setOpen] = useState(false);
  const router = useRouter();

  const handleClick = () => {
    setOpen(!open);
  };

  const theme = useTheme();

  const handleRoute = (path) => {
    router.push(path);
  };

  const joinUsItems = [
    { text: t("Seller Login"), link: `${process.env.NEXT_PUBLIC_BASE_URL}/login/seller` },
    { text: t("Become a Seller"), link: "/store-registration?active=active" },
    { text: t("Become a Rider"), link: "/rider-registration" },
  ];

  return (
    <List component="nav">
      <ListItemButton onClick={handleClick}>
        <ListItemText primary={t("Join Us")} sx={{ color: theme.palette.primary.main }} />
        {open ? <ExpandLess sx={{ color: theme.palette.primary.main }} /> : <ExpandMore sx={{ color: theme.palette.primary.main }} />}
      </ListItemButton>

      <Collapse in={open} timeout="auto" unmountOnExit>
        <List component="div" disablePadding>
          {joinUsItems.map((item, index) => (
            <ListItemButton
              key={index}
              sx={{ pl: 4 }}
              onClick={() => handleRoute(item.link)}
            >
              <ListItemText primary={item.text} sx={{ color: theme.palette.primary.main }} />
            </ListItemButton>
          ))}
        </List>
      </Collapse>
    </List>
  );
};

export default JoinUsDropdown;
