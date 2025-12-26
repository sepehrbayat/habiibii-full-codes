import React from "react";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import CustomContainer from "../../../container";
import SalonList from "./components/SalonList";
import useGetPopularSalons from "../../../../api-manage/hooks/react-query/beauty/useGetPopularSalons";
import useGetTopRatedSalons from "../../../../api-manage/hooks/react-query/beauty/useGetTopRatedSalons";
import { BeautyApi } from "../../../../api-manage/another-formated-api/beautyApi";
import { useQuery } from "react-query";
import { Grid, Typography } from "@mui/material";
import SalonCard from "./components/SalonCard";

const Beauty = ({ configData }) => {
  const { data: popularSalons, isLoading: popularLoading } = useGetPopularSalons();
  const { data: topRatedSalons, isLoading: topRatedLoading } = useGetTopRatedSalons();
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth() + 1;

  const { data: monthlyTopRated, isLoading: monthlyTopRatedLoading } = useQuery(
    ["beauty-monthly-top-rated", currentYear, currentMonth],
    () => BeautyApi.getMonthlyTopRatedSalons(currentYear, currentMonth),
    { enabled: true }
  );

  const { data: trendingClinics, isLoading: trendingClinicsLoading } = useQuery(
    ["beauty-trending-clinics", currentYear, currentMonth],
    () => BeautyApi.getTrendingClinics(currentYear, currentMonth),
    { enabled: true }
  );

  return (
    <CustomStackFullWidth>
      <CustomContainer>
        <CustomStackFullWidth spacing={4} sx={{ py: 4 }}>
          {/* Trending Clinics Section */}
          {trendingClinics?.data && trendingClinics.data.length > 0 && (
            <CustomStackFullWidth spacing={2}>
              <Typography variant="h4" fontWeight="bold">
                Trending Clinics
              </Typography>
              <Grid container spacing={3}>
                {trendingClinics.data.slice(0, 6).map((salon) => (
                  <Grid item xs={12} sm={6} md={4} key={salon.id}>
                    <SalonCard salon={salon} />
                  </Grid>
                ))}
              </Grid>
            </CustomStackFullWidth>
          )}

          {/* Monthly Top Rated Section */}
          {monthlyTopRated?.data && monthlyTopRated.data.length > 0 && (
            <CustomStackFullWidth spacing={2}>
              <Typography variant="h4" fontWeight="bold">
                Monthly Top Rated
              </Typography>
              <Grid container spacing={3}>
                {monthlyTopRated.data.slice(0, 6).map((salon) => (
                  <Grid item xs={12} sm={6} md={4} key={salon.id}>
                    <SalonCard salon={salon} />
                  </Grid>
                ))}
              </Grid>
            </CustomStackFullWidth>
          )}

          {/* Popular Salons Section */}
          {popularSalons?.data && popularSalons.data.length > 0 && (
            <CustomStackFullWidth spacing={2}>
              <SalonList
                title="Popular Salons"
                salons={popularSalons.data}
                isLoading={popularLoading}
              />
            </CustomStackFullWidth>
          )}

          {/* Top Rated Salons Section */}
          {topRatedSalons?.data && topRatedSalons.data.length > 0 && (
            <CustomStackFullWidth spacing={2}>
              <SalonList
                title="Top Rated Salons"
                salons={topRatedSalons.data}
                isLoading={topRatedLoading}
              />
            </CustomStackFullWidth>
          )}

          {/* Search and Filter Section */}
          <SalonList
            title="All Salons"
            showSearch={true}
            showFilters={true}
          />
        </CustomStackFullWidth>
      </CustomContainer>
    </CustomStackFullWidth>
  );
};

export default Beauty;

