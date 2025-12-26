/* eslint-disable react-hooks/exhaustive-deps */
/* eslint-disable jsx-a11y/alt-text */
/* eslint-disable react/jsx-no-undef */
import { useTheme } from "@emotion/react";
import {
  Button,
  Chip,
  MenuItem,
  Select,
  Tooltip,
  styled,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
} from "@mui/material";
import { Box, Stack } from "@mui/system";
import moment from "moment";
import Image from "next/image";
import Link from "next/link";
import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { useInView } from "react-intersection-observer";
import SimpleBar from "simplebar-react";
import { data_limit } from "../../api-manage/ApiRoutes";
import { getAmountWithSign } from "../../helper-functions/CardHelpers";
import CustomDivider from "../CustomDivider";
import DotSpin from "../DotSpin";
import CustomEmptyResult from "../custom-empty-result";
import nodataimage from "./img/noData.svg";
import TransactionShimmer from "./Shimmer";
import greenCoin from "./img/green-coin.png";
import yellowCoin from "./img/yellow-coin.png";

export const transaction_options = [
  {
    label: "All Transactions",
    value: "all",
  },
  {
    label: "Order Transaction",
    value: "order",
  },
  {
    label: "Add Fund",
    value: "add_fund",
  },
  {
    label: "Loyalty Points Transaction",
    value: "loyalty_point",
  },
  {
    label: "Referrer Transactions",
    value: "referrer",
  },
  {
    label: "Cash back Transactions",
    value: "CashBack",
  },
];

const TransactionHistory = (props) => {
  const {
    data,
    isLoading,
    page,
    value,
    setValue,
    offset,
    setOffset,
    isFetching,
  } = props;

  const [trxData, setTrxData] = useState([]);

  useEffect(() => {
    if (!isLoading) {
      if (offset <= 1) {
        setTrxData(data?.data);
      } else {
        setTrxData([...trxData, ...data?.data]);
      }
    }
  }, [data]);
  const { ref, inView } = useInView();

  useEffect(() => {
    if (inView) {
      if (!isLoading) {
        if (offset * data_limit <= data.total_size) {
          setOffset((prevState) => prevState + 1);
        }
      }
    }
  }, [inView]);

  const { t } = useTranslation();

  const handleChange = (e) => {
    setValue(e.target.value);
    setOffset(1);
  };
  const theme = useTheme();
  return (
    <>
      <Stack
        direction="row"
        justifyContent="space-between"
        alignItems="center"
        gap={2}
        mt={2}
      >
        <Typography fontSize="18px" fontWeight="700" py="1rem" m={0}>
          {t("Transaction History")}
        </Typography>
        {page != "loyalty" && (
          <CustomSelect value={value} onChange={(e) => handleChange(e)}>
            {transaction_options?.map((item, i) => (
              <MenuItem key={i} value={item?.value}>
                {t(item?.label)}
              </MenuItem>
            ))}
          </CustomSelect>
        )}
      </Stack>
      {trxData?.length > 0 && (
        <SimpleBar style={{ maxHeight: "60vh" }}>
          <TableContainer>
            <CustomTable>
              <TableHead>
                <TableRow
                  sx={{
                    borderRadius: "10px",
                    background: theme.palette.primary.lite,
                  }}
                >
                  <CustomTableCell>
                    <Typography
                      variant="body1"
                      color={theme.palette.text.primary}
                    >
                      {t("Transaction Type")}
                    </Typography>
                  </CustomTableCell>
                  <CustomTableCell>
                    <Typography
                      variant="body1"
                      color={theme.palette.text.primary}
                      textTransform="capitalize"
                    >
                      {page == "loyalty" ? t("points") : t("Amount")}
                    </Typography>
                  </CustomTableCell>
                  <CustomTableCell>
                    <Typography
                      variant="body1"
                      color={theme.palette.text.primary}
                    >
                      {t("Date & Time")}
                    </Typography>
                  </CustomTableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {trxData?.map((item, index) => {
                  const isDebit = item?.debit && Number(item?.debit) > 0;
                  const amountValue = isDebit
                    ? item?.debit
                    : (item?.credit || 0) + (item?.admin_bonus || 0);
                  const formattedAmount = getAmountWithSign(amountValue);
                  const amountWithSign = `${isDebit ? "-" : "+"} ${formattedAmount}`;
                  return (
                    <TableRow
                      key={item?.id}
                      sx={{
                        ".MuiTableCell-root": {
                          background: theme.palette.background.custom3,
                          borderRadius: "10px",
                        },
                      }}
                    >
                      <CustomTableCell>
                        <Stack direction="row" gap="4px" alignItems="center">
                          {item?.debit ? (
                            <Image
                              src={yellowCoin.src}
                              width="16"
                              height="16"
                              alt="image"
                            />
                          ) : (
                            <Image
                              src={greenCoin.src}
                              width="16"
                              height="16"
                              alt="image"
                            />
                          )}
                          {item?.transaction_type === "add_fund" ? (
                            <Typography
                              fontSize="12px"
                              sx={{
                                color: theme.palette.text.secondary,
                              }}
                            >
                              {t("added via ")}
                              {t(item?.reference).replaceAll("_", " ")} (
                              {t("bonus")}:
                              {getAmountWithSign(item?.admin_bonus)})
                            </Typography>
                          ) : (
                            <Typography
                              fontSize="12px"
                              sx={{
                                color: theme.palette.text.secondary,
                              }}
                            >
                              {t(item?.transaction_type).replaceAll("_", " ")}
                            </Typography>
                          )}
                        </Stack>
                      </CustomTableCell>
                      <CustomTableCell>
                        <Stack direction="row" alignItems="center" spacing={1}>
                        <Typography
                          sx={{
                            fontSize: "14px",
                            fontWeight: "500",
                              color: isDebit
                                ? theme.palette.error.main
                                : theme.palette.success.main,
                          }}
                        >
                          {page == "loyalty" ? (
                            item?.transaction_type === "point_to_wallet"
                              ? item?.debit
                              : item?.credit
                          ) : (
                            <>
                                {amountWithSign}
                                {item?.balance_after !== undefined && (
                                  <Chip
                                    size="small"
                                    label={`${t("Balance")}: ${getAmountWithSign(
                                      item?.balance_after
                                    )}`}
                                    sx={{
                                      ml: 1,
                                      height: 22,
                                      fontSize: "11px",
                                    }}
                                  />
                              )}
                            </>
                          )}
                        </Typography>
                          {page !== "loyalty" && (
                            <Chip
                              size="small"
                              variant="outlined"
                              color={isDebit ? "error" : "success"}
                              label={isDebit ? t("debit") : t("credit")}
                            />
                          )}
                        </Stack>
                      </CustomTableCell>
                      <CustomTableCell>
                        <Tooltip title={moment(item?.created_at).format("LLLL")}>
                          <span>
                            {moment(item?.created_at).format("DD MMM YYYY, h:mm A")}
                          </span>
                        </Tooltip>
                      </CustomTableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </CustomTable>
            <Box ref={ref} sx={{ height: "5px" }} />
            {!isLoading && isFetching && (
              <Stack sx={{ py: 1, alignItems: "center" }}>
                <Typography variant="body2" color="text.secondary">
                  {t("Loading more...")}
                </Typography>
              </Stack>
            )}
          </TableContainer>
        </SimpleBar>
      )}
      {!isLoading && isFetching && (
        <Stack sx={{ marginTop: "2rem" }}>
          <DotSpin />
        </Stack>
      )}
      {isLoading && (
        <TableContainer>
          <CustomTable>
            <TableBody>
              <TransactionShimmer />
            </TableBody>
          </CustomTable>
        </TableContainer>
      )}
      {trxData?.length == 0 && (
        <Stack spacing={2} alignItems="center">
        <CustomEmptyResult
          image={nodataimage}
          width="128px"
          height="80"
          label="No transaction found"
        />
          <Typography color="text.secondary" textAlign="center">
            {t(
              "You have no wallet activity yet. Add funds or explore services to start."
            )}
          </Typography>
          <Stack direction="row" spacing={2}>
            <Link href="/beauty" passHref legacyBehavior>
              <Button variant="contained" color="primary">
                {t("Explore services")}
              </Button>
            </Link>
            <Link href="/wallet" passHref legacyBehavior>
              <Button variant="outlined" color="primary">
                {t("Add funds")}
              </Button>
            </Link>
          </Stack>
        </Stack>
      )}
      <CustomDivider />
    </>
  );
};

export const CustomSelect = ({
  label,
  children,
  name,
  id,
  value,
  onChange,
}) => {
  return (
    <Select
      labelId={id}
      id={id}
      name={name}
      value={value}
      label={label}
      onChange={onChange}
      sx={{ height: "45px" }}
    >
      {children}
    </Select>
  );
};

export const CustomTableCell = styled(TableCell)(({ theme }) => ({
  padding: "17px 40px",
  textTransform: "capitalize !important",
  borderBottom: "none",
  borderRadius: "0 !important",
  "&:first-child": {
    borderRadius: "10px 0 0 10px !important",
  },
  "&:last-child": {
    borderRadius: "0 10px 10px 0 !important",
  },
  [theme.breakpoints.down("md")]: {
    padding: "17px 15px",
  },
}));
export const CustomTable = styled(Table)(({ theme }) => ({
  borderCollapse: "separate",
  borderSpacing: "0 15px",
  borderRadius: "5px",
}));
export default TransactionHistory;
