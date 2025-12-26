import React, { useMemo, useState } from "react";
import { Typography, Grid, Box, CircularProgress, TextField, Pagination } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import PackageCard from "./PackageCard";
import useGetPackages from "../../../../../api-manage/hooks/react-query/beauty/useGetPackages";
import { useRouter } from "next/router";

const PackageList = () => {
  const router = useRouter();
  const [salonId, setSalonId] = useState("");
  const [serviceId, setServiceId] = useState("");
  const [page, setPage] = useState(1);
  const limit = 12;
  const offset = (page - 1) * limit;
  const { data, isLoading } = useGetPackages(
    {
      salon_id: salonId || undefined,
      service_id: serviceId || undefined,
      limit,
      offset,
    },
    true
  );

  const packages = data?.data || [];
  const totalPages = useMemo(() => {
    if (!data?.total) return 1;
    return Math.max(1, Math.ceil(data.total / limit));
  }, [data?.total, limit]);

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Beauty Packages
      </Typography>

      <TextField
        fullWidth
        label="Filter by Salon ID (Optional)"
        value={salonId}
        onChange={(e) => setSalonId(e.target.value)}
        type="number"
      />
      <TextField
        fullWidth
        label="Filter by Service ID (Optional)"
        value={serviceId}
        onChange={(e) => setServiceId(e.target.value)}
        type="number"
      />

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : packages.length > 0 ? (
        <>
          <Grid container spacing={3}>
            {packages.map((pkg) => (
              <Grid item xs={12} sm={6} md={4} key={pkg.id}>
                <PackageCard
                  package={pkg}
                  onViewDetails={(id) => router.push(`/beauty/packages/${id}`)}
                />
              </Grid>
            ))}
          </Grid>
          {totalPages > 1 && (
            <Box display="flex" justifyContent="center" mt={3}>
              <Pagination
                count={totalPages}
                page={page}
                onChange={(event, value) => setPage(value)}
                color="primary"
              />
            </Box>
          )}
        </>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No packages found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default PackageList;

