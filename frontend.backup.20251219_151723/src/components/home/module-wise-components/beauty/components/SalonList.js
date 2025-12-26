import React, { useState } from "react";
import { Typography, Grid, Box, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import SalonCard from "./SalonCard";
import SalonSearch from "./SalonSearch";
import SalonFilters from "./SalonFilters";
import useGetSalons from "../../../../../api-manage/hooks/react-query/beauty/useGetSalons";

const SalonList = ({ title, salons: initialSalons, isLoading: initialLoading, showSearch = false, showFilters = false }) => {
  const [searchParams, setSearchParams] = useState({});
  const { data, isLoading } = useGetSalons(searchParams, showSearch || showFilters);
  
  const salons = initialSalons || data?.data || [];
  const loading = initialLoading || isLoading;

  return (
    <CustomStackFullWidth spacing={2}>
      {title && (
        <Typography variant="h5" fontWeight="bold">
          {title}
        </Typography>
      )}
      
      {showSearch && (
        <SalonSearch onSearch={(params) => setSearchParams(params)} />
      )}
      
      {showFilters && (
        <SalonFilters onFilter={(params) => setSearchParams(prev => ({ ...prev, ...params }))} />
      )}

      {loading ? (
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
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No salons found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default SalonList;

