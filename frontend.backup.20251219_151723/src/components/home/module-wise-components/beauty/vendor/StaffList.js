import React, { useState } from "react";
import { Typography, Box, CircularProgress, IconButton, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import StaffCard from "./StaffCard";
import { useGetVendorStaff } from "../../../../../api-manage/hooks/react-query/beauty/vendor/useGetVendorStaff";
import { getVendorToken } from "helper-functions/getToken";
import { useRouter } from "next/router";
import AddIcon from "@mui/icons-material/Add";

const StaffList = () => {
  const vendorToken = getVendorToken();
  const router = useRouter();
  const [page, setPage] = useState(0);
  const limit = 25;

  const { data, isLoading, refetch } = useGetVendorStaff(
    {
      limit: limit,
      offset: page * limit,
    },
    !!vendorToken
  );

  if (!vendorToken) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view staff</Typography>
      </Box>
    );
  }

  const staffList = data?.data || [];

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Typography variant="h4" fontWeight="bold">
          Staff Management
        </Typography>
        <Button
          variant="contained"
          color="primary"
          startIcon={<AddIcon />}
          onClick={() => router.push("/beauty/vendor/staff/create")}
        >
          Add Staff
        </Button>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : staffList.length > 0 ? (
        <CustomStackFullWidth spacing={2}>
          {staffList.map((staff) => (
            <StaffCard
              key={staff.id}
              staff={staff}
              onViewDetails={(id) => router.push(`/beauty/vendor/staff/${id}`)}
              onRefetch={refetch}
            />
          ))}
        </CustomStackFullWidth>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No staff members found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default StaffList;

