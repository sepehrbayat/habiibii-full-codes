import React from "react";
import { Card, CardContent, Skeleton, Box } from "@mui/material";

const PackageCardSkeleton = () => {
  return (
    <Card sx={{ height: "100%", display: "flex", flexDirection: "column" }}>
      <CardContent sx={{ flexGrow: 1 }}>
        <Skeleton variant="text" width="80%" height={32} />
        <Skeleton variant="text" width="100%" height={60} sx={{ mt: 1 }} />
        <Skeleton variant="text" width="40%" height={28} sx={{ mt: 2 }} />
        <Skeleton variant="text" width="60%" height={20} sx={{ mt: 1 }} />
        <Skeleton variant="rectangular" width={80} height={24} sx={{ mt: 2 }} />
        <Skeleton variant="rectangular" width="100%" height={40} sx={{ mt: 3 }} />
      </CardContent>
    </Card>
  );
};

export default PackageCardSkeleton;

