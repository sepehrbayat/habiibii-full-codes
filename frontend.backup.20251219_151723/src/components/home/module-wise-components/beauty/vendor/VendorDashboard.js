import React from "react";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import DashboardStats from "./DashboardStats";
import RecentBookings from "./RecentBookings";

const VendorDashboard = () => {
  return (
    <CustomStackFullWidth spacing={4} sx={{ py: 4 }}>
      <DashboardStats />
      <RecentBookings />
    </CustomStackFullWidth>
  );
};

export default VendorDashboard;

