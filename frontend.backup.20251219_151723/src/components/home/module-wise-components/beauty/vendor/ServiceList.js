import React from "react";
import { Typography, Box, CircularProgress, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import ServiceCard from "./ServiceCard";
import { useGetVendorServices } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorServices";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";
import AddIcon from "@mui/icons-material/Add";

const ServiceList = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();

  const { data, isLoading, refetch } = useGetVendorServices(
    {
      limit: 25,
      offset: 0,
    },
    !!vendorToken
  );

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view services</Typography>
      </Box>
    );
  }

  const services = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h4" fontWeight="bold">
          Service Management
        </Typography>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={() => router.push("/beauty/vendor/services/create")}
        >
          Add Service
        </Button>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : services.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {services.map((service) => (
            <ServiceCard
              key={service.id}
              service={service}
              onViewDetails={(id) => router.push(`/beauty/vendor/services/${id}`)}
              onRefetch={refetch}
            />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No services found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default ServiceList;

