import React from "react";
import { Card, CardContent, Skeleton, Box } from "@mui/material";

const BookingCardSkeleton = () => {
  return (
    <Card>
      <CardContent>
        <Box display="flex" justifyContent="space-between" mb={2}>
          <Box flex={1}>
            <Skeleton variant="text" width="70%" height={28} />
            <Skeleton variant="text" width="50%" height={20} sx={{ mt: 1 }} />
          </Box>
          <Skeleton variant="rectangular" width={80} height={24} />
        </Box>
        <Skeleton variant="text" width="100%" height={20} />
        <Skeleton variant="text" width="80%" height={20} sx={{ mt: 1 }} />
        <Skeleton variant="text" width="60%" height={20} sx={{ mt: 1 }} />
        <Skeleton variant="rectangular" width="100%" height={36} sx={{ mt: 2 }} />
      </CardContent>
    </Card>
  );
};

export default BookingCardSkeleton;

