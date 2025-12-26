import React, { useMemo, useState } from "react";
import {
  Box,
  Card,
  CardContent,
  CircularProgress,
  Grid,
  MenuItem,
  Select,
  Typography,
  FormControl,
  InputLabel,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useQuery } from "react-query";
import { BeautyApi } from "../../../../../api-manage/another-formated-api/beautyApi";
import SalonCard from "./SalonCard";
import dayjs from "dayjs";

const MonthlyTopRatedSalons = () => {
  const currentYear = dayjs().year();
  const currentMonth = dayjs().month() + 1;
  const [year, setYear] = useState(currentYear);
  const [month, setMonth] = useState(currentMonth);

  const { data, isLoading } = useQuery(
    ["beauty-monthly-top-rated", year, month],
    async () => {
      const response = await BeautyApi.getMonthlyTopRatedSalons(year, month);
      return response.data;
    },
    { keepPreviousData: true }
  );

  const salons = useMemo(() => data?.data || [], [data]);

  const years = Array.from({ length: 5 }).map((_, idx) => currentYear - idx);

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Monthly Top-Rated Salons
      </Typography>

      <Box display="flex" gap={2} flexWrap="wrap">
        <FormControl sx={{ minWidth: 140 }}>
          <InputLabel>Year</InputLabel>
          <Select value={year} label="Year" onChange={(e) => setYear(e.target.value)}>
            {years.map((y) => (
              <MenuItem key={y} value={y}>
                {y}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
        <FormControl sx={{ minWidth: 140 }}>
          <InputLabel>Month</InputLabel>
          <Select value={month} label="Month" onChange={(e) => setMonth(e.target.value)}>
            {Array.from({ length: 12 }).map((_, idx) => (
              <MenuItem key={idx + 1} value={idx + 1}>
                {dayjs().month(idx).format("MMMM")}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : salons.length > 0 ? (
        <Grid container spacing={3}>
          {salons.map((salon) => (
            <Grid item xs={12} sm={6} md={4} lg={3} key={salon.id}>
              <SalonCard salon={salon} />
            </Grid>
          ))}
        </Grid>
      ) : (
        <Card>
          <CardContent>
            <Typography variant="body1" color="text.secondary">
              No top-rated salons for this period.
            </Typography>
          </CardContent>
        </Card>
      )}
    </CustomStackFullWidth>
  );
};

export default MonthlyTopRatedSalons;

