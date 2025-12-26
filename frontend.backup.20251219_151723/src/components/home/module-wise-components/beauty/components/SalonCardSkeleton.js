import React from "react";
import { Card, CardContent, Skeleton, Box } from "@mui/material";

const SalonCardSkeleton = () => {
  return (
    <Card>
      <Skeleton variant="rectangular" height={200} />
      <CardContent>
        <Skeleton variant="text" width="60%" height={32} />
        <Skeleton variant="text" width="40%" height={24} sx={{ mt: 1 }} />
        <Box display="flex" gap={1} mt={2}>
          <Skeleton variant="rectangular" width={60} height={24} />
          <Skeleton variant="rectangular" width={60} height={24} />
        </Box>
        <Skeleton variant="rectangular" width="100%" height={40} sx={{ mt: 2 }} />
      </CardContent>
    </Card>
  );
};

export default SalonCardSkeleton;

