/* eslint-disable react-hooks/exhaustive-deps */
import InfoOutlinedIcon from "@mui/icons-material/InfoOutlined";
import PaidIcon from "@mui/icons-material/Paid";
import {
        Button,
        Grid,
        Popover,
        Typography,
        alpha,
        useMediaQuery,
        useTheme,
} from "@mui/material";
import { Stack } from "@mui/system";
import { t } from "i18next";
import React, { useEffect, useRef, useState } from "react";
import { useDispatch } from "react-redux";
import useGetLoyaltyPointTransactionsList from "../../api-manage/hooks/react-query/loyalty-points/useGetLoyaltyPointTransactionList";
import useGetProfile from "../../api-manage/hooks/react-query/profile/useGetProfile";
import { setUser } from "../../redux/slices/profileInfo";
import { CustomStackFullWidth } from "../../styled-components/CustomStyles.style";
import TransactionHistory from "../transaction-history";
import HowToUse from "../wallet/HowToUse";
import TransactionHistoryMobile from "../wallet/TransactionHistoryMobile";
import WalletBoxComponent from "../wallet/WalletBoxComponent";
import trophy from "./assets/loyaltyimg.png";
import LoyaltyModal from "./loyalty-modal";

const LoyaltyPoints = (props) => {
        const { configData } = props;
        const [offset, setOffset] = useState(1);
        const [openModal, setOpenModal] = useState(false);
        const theme = useTheme();
        const isSmall = useMediaQuery(theme.breakpoints.down("md"));
        const dispatch = useDispatch();
        const [openPopover, setOpenPopover] = useState(false);
        const anchorRef = useRef(null);
        const userOnSuccessHandler = (res) => {
                dispatch(setUser(res));
        };
        const { data: userData, refetch: profileRefetch } =
                useGetProfile(userOnSuccessHandler);
        let pageParams = { offset: offset };
        const { data, refetch, isLoading, isFetching } =
                useGetLoyaltyPointTransactionsList(pageParams);
        useEffect(() => {
                fetchData();
        }, []);
        useEffect(() => {
                refetch();
        }, [offset]);

        const fetchData = async () => {
                await profileRefetch();
                await refetch();
        };
        const handleConvertCurrency = () => {
                setOpenModal(true);
        };

        const steps = [
                {
                        label: "Convert your loyalty point to wallet money.",
                },
                {
                        label: "Minimum 200 points required to convert into currency",
                },
        ];

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
                        return false;
                }
        };

        const anchorEl = anchorRef?.current;
        const isValidAnchor = anchorEl && isAnchorElValid(anchorEl);
        const shouldOpenPopover = openPopover && isValidAnchor;

        return (
                <CustomStackFullWidth
                        my={{ xs: "1rem", md: "2rem" }}
                        alignItems="center"
                        justifyContent="space-between"
                        sx={{ height: "100%", minHeight: "60vh", alignItems: "center" }}
                >
                        <Grid
                                container
                                pl={{ xs: "10px", md: "20px" }}
                                pr={{ xs: "10px", md: "20px" }}
                                justifyContent="space-between"
                        >
                                <Grid
                                        xs={12}
                                        md={4.5}
                                        align="left"
                                        borderRight={{
                                                xs: "none",
                                                md: `2px solid ${alpha(theme.palette.neutral[400], 0.3)}`,
                                        }}
                                >
                                        <Stack spacing={{ xs: 2, md: 5 }}>
                                                {isSmall && (
                                                        <Stack direction="row" justifyContent="space-between">
                                                                <Typography
                                                                        textTransform="capitalize"
                                                                        fontWeight="700"
                                                                        fontSize="16px"
                                                                >
                                                                        {t("Loyalty point")}
                                                                </Typography>
                                                                <InfoOutlinedIcon
                                                                        ref={anchorRef}
                                                                        onClick={() => setOpenPopover(true)}
                                                                        sx={{ cursor: "pointer" }}
                                                                />
                                                        </Stack>
                                                )}
                                                <WalletBoxComponent
                                                        balance={userData?.loyalty_point}
                                                        title={t("Total points")}
                                                        image={trophy}
                                                        handleConvertCurrency={handleConvertCurrency}
                                                        isSmall={isSmall}
                                                />
                                                {!isSmall && <HowToUse steps={steps} />}
                                                {isSmall && (
                                                        <Stack alignItems="center">
                                                                <Button
                                                                        variant="contained"
                                                                        startIcon={<PaidIcon />}
                                                                        // style={{ color: textColor }}

                                                                        sx={{
                                                                                borderRadius: "10px",
                                                                                width: "186px",
                                                                                fontSize: "12px",
                                                                        }}
                                                                        onClick={() => handleConvertCurrency()}
                                                                >
                                                                        {t("Convert to Currency")}
                                                                </Button>
                                                        </Stack>
                                                )}
                                        </Stack>
                                </Grid>

                                <Grid
                                        item
                                        xs={12}
                                        md={7.5}
                                        paddingLeft={{ xs: "0px", sm: "30px", md: "60px" }}
                                >
                                        {isSmall ? (
                                                <TransactionHistoryMobile
                                                        data={data}
                                                        isLoading={isLoading}
                                                        page="loyalty"
                                                        isFetching={isFetching}
                                                        offset={offset}
                                                        setOffset={setOffset}
                                                />
                                        ) : (
                                                <TransactionHistory
                                                        data={data}
                                                        isLoading={isLoading}
                                                        page="loyalty"
                                                        isFetching={isFetching}
                                                        offset={offset}
                                                        setOffset={setOffset}
                                                />
                                        )}
                                </Grid>
                        </Grid>

                        {openModal && (
                                <LoyaltyModal
                                        configData={configData}
                                        theme={theme}
                                        t={t}
                                        openModal={openModal}
                                        handleClose={() => setOpenModal(false)}
                                        loyalitydata={userData?.loyalty_point}
                                        refetch={refetch}
                                        profileRefetch={profileRefetch}
                                />
                        )}
                        {/* Only render Popover if anchorEl is valid */}
                        {/* فقط رندر Popover اگر anchorEl معتبر باشد */}
                        {isValidAnchor && (
                                <Popover
                                        disableScrollLock={true}
                                        anchorEl={anchorEl}
                                        onClose={() => setOpenPopover(false)}
                                        anchorOrigin={{
                                                vertical: "top",
                                                horizontal: "left",
                                        }}
                                        transformOrigin={{
                                                vertical: "top",
                                                horizontal: "left",
                                        }}
                                        keepMounted
                                        open={shouldOpenPopover}
                                        PaperProps={{
                                                sx: {
                                                        borderRadius: "0px",
                                                        top: "244px !important",
                                                        padding: "20px",
                                                },
                                        }}
                                        transitionDuration={2}
                                        // Add error handler for positioning
                                        // افزودن error handler برای positioning
                                        onError={(error) => {
                                                console.warn("Popover positioning error:", error);
                                                setOpenPopover(false);
                                        }}
                                >
                                        <Stack>
                                                <HowToUse steps={steps} />
                                        </Stack>
                                </Popover>
                        )}
                </CustomStackFullWidth>
        );
};

export default LoyaltyPoints;

