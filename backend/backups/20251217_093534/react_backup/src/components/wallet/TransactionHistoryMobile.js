import { useTheme } from "@emotion/react";
import {
  Button,
  Chip,
  MenuItem,
  Tooltip,
  Typography,
} from "@mui/material";
import { Box, Stack } from "@mui/system";
import Link from "next/link";
import moment from "moment";
import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { useInView } from "react-intersection-observer";
import { data_limit } from "../../api-manage/ApiRoutes";
import { getAmountWithSign } from "../../helper-functions/CardHelpers";
import { CustomStackFullWidth } from "../../styled-components/CustomStyles.style";
import DotSpin from "../DotSpin";
import CustomEmptyResult from "../custom-empty-result";
import nodataimage from "../loyalty-points/assets/Search.svg";
import { CustomSelect, transaction_options } from "../transaction-history";
import TransactionShimmer from "../transaction-history/Shimmer";

const TransactionHistoryMobile = ({
  data,
  isLoading,
  page,
  value,
  setValue,
  offset,
  setOffset,
  isFetching,
}) => {
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
        if (offset * data_limit <= data?.total_size) {
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
    <CustomStackFullWidth>
      <Stack
        direction="row"
        justifyContent="space-between"
        alignItems="center"
        gap={2}
        mt={2}
        mb={2}
      >
        <Typography fontSize="14px" fontWeight="700" py="1rem">
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
      {trxData?.length > 0 &&
        trxData?.map((item) => {
          const isDebit = item?.debit && Number(item?.debit) > 0;
          const amountValue = isDebit
            ? item?.debit
            : (item?.credit || 0) + (item?.admin_bonus || 0);
          const formattedAmount = getAmountWithSign(amountValue);
          const amountWithSign = `${isDebit ? "-" : "+"} ${formattedAmount}`;
          return (
            <Stack
              key={item?.id}
              spacing={1.8}
              padding="14px 11px"
              backgroundColor={theme.palette.neutral[300]}
              borderRadius="10px"
              marginBottom="10px"
            >
              <Stack direction="row" justifyContent="space-between" alignItems="center">
                <Typography
                  color={
                    isDebit
                      ? (theme) => theme.palette.error.main
                      : (theme) => theme.palette.success.main
                  }
                  fontWeight="500"
                  fontSize="14px"
                >
                  {page === "loyalty"
                    ? item?.transaction_type === "point_to_wallet"
                      ? item?.debit
                      : item?.credit
                    : amountWithSign}
                </Typography>
                {page !== "loyalty" && (
                  <Chip
                    size="small"
                    variant="outlined"
                    color={isDebit ? "error" : "success"}
                    label={isDebit ? t("debit") : t("credit")}
                  />
                )}
                <Typography fontSize="13px" color={theme.palette.neutral[400]}>
                  <Tooltip title={moment(item?.created_at).format("LLLL")}>
                    <span>
                      {moment(item?.created_at).format("DD MMM YYYY, h:mm A")}
                    </span>
                  </Tooltip>
                </Typography>
              </Stack>
              <Stack direction="row" justifyContent="space-between">
                {item?.transaction_type === "add_fund" ? (
                  <Typography fontSize="12px">
                    {t("added via")}
                    {t(item?.reference).replaceAll("_", " ")} ({t("bonus")}:
                    {getAmountWithSign(item?.admin_bonus)})
                  </Typography>
                ) : (
                  <Typography fontSize="13px">
                    {t(item?.transaction_type).replaceAll("_", " ")}
                  </Typography>
                )}
                {item?.balance_after !== undefined && (
                  <Chip
                    size="small"
                    label={`${t("Balance")}: ${getAmountWithSign(
                      item?.balance_after
                    )}`}
                    sx={{
                      height: 22,
                      fontSize: "11px",
                    }}
                  />
                )}
              </Stack>
            </Stack>
          );
        })}
      {!isLoading && isFetching && (
        <Stack sx={{ marginTop: "2rem" }}>
          <DotSpin />
        </Stack>
      )}
      <Box ref={ref} sx={{ height: "5px" }} />
      {!isLoading && isFetching && (
        <Stack sx={{ py: 1, alignItems: "center" }}>
          <Typography variant="body2" color="text.secondary">
            {t("Loading more...")}
          </Typography>
        </Stack>
      )}

      {trxData?.length == 0 && (
        <Stack spacing={2} alignItems="center" mt={2}>
        <CustomEmptyResult
          image={nodataimage}
          width="128px"
          height="128px"
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
      {isLoading && <TransactionShimmer />}
    </CustomStackFullWidth>
  );
};

export default TransactionHistoryMobile;
