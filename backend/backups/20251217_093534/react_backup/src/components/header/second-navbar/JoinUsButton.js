import React, { useState } from "react";
import { Button, Menu, MenuItem } from "@mui/material";
import StorefrontIcon from "@mui/icons-material/Storefront";
import { NavLinkStyle } from "../NavBar.style";
import Link from "next/link";
import { t } from "i18next";


const isUserLoggedIn = () => {
    return Boolean(localStorage.getItem("token"));
};

const JoinUsButton = () => {
    const [anchorEl, setAnchorEl] = useState(null);


    const handleMenuOpen = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleMenuClose = () => {
        setAnchorEl(null);
    };


    if (!isUserLoggedIn()) {
        return (
            <>
                <Button
                    onClick={handleMenuOpen}
                    variant="outlined"
                    sx={{
                        textTransform: "none",
                        borderColor: "primary.main",
                        color: "primary.main",
                        padding: "6px 2px",
                        borderRadius: "30px",
                        fontWeight: "bold",
                        display: "flex",
                        alignItems: "center",
                        gap: "8px",
                        width: 140
                    }}
                >
                    <StorefrontIcon sx={{ fontSize: 20 }} />
                    {t("Seller | Rider")}
                </Button>

                <Menu anchorEl={anchorEl} open={Boolean(anchorEl)} onClose={handleMenuClose}>
                    <MenuItem
                        onClick={handleMenuClose}
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
        );
    }


    return null;
};

export default JoinUsButton;