import React from "react";
import { Typography, Box } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const PackageUsageChart = ({ data }) => {
  // Placeholder for chart component
  return (
    <CustomStackFullWidth spacing={2}>
      <Typography variant="h6">Usage Trends</Typography>
      <Box
        sx={{
          height: 300,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          border: "1px dashed",
          borderColor: "divider",
        }}
      >
        <Typography variant="body2" color="text.secondary">
          Chart visualization would go here
        </Typography>
      </Box>
    </CustomStackFullWidth>
  );
};

export default PackageUsageChart;

