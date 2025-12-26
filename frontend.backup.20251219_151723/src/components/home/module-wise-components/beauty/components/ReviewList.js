import React, { useState } from "react";
import { Typography, Box, Tabs, Tab, CircularProgress, Pagination } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import ReviewCard from "./ReviewCard";
import useGetUserReviews from "../../../../../api-manage/hooks/react-query/beauty/useGetUserReviews";
import { getToken } from "helper-functions/getToken";
import { useSelector } from "react-redux";

const ReviewList = () => {
  const token = getToken();
  const { configData } = useSelector((state) => state.configData);
  const [tab, setTab] = useState(0);
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;
  const offset = (page - 1) * limit;

  const { data, isLoading, refetch } = useGetUserReviews(
    {
      limit,
      offset,
      status: status || undefined,
    },
    !!token
  );

  const handleTabChange = (event, newValue) => {
    setTab(newValue);
    setPage(1);
    if (newValue === 0) {
      setStatus("");
    } else if (newValue === 1) {
      setStatus("approved");
    } else if (newValue === 2) {
      setStatus("pending");
    } else if (newValue === 3) {
      setStatus("rejected");
    }
  };

  if (!token) {
    return (
      <Box p={4} textAlign="center">
        <Typography variant="h6">Please login to view your reviews</Typography>
      </Box>
    );
  }

  const reviews = data?.data || [];
  const totalPages = Math.ceil((data?.total || 0) / limit);

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        My Reviews
      </Typography>

      <Tabs value={tab} onChange={handleTabChange}>
        <Tab label="All" />
        <Tab label="Approved" />
        <Tab label="Pending" />
        <Tab label="Rejected" />
      </Tabs>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : reviews.length > 0 ? (
        <>
          <CustomStackFullWidth spacing={2}>
            {reviews.map((review) => (
              <ReviewCard key={review.id} review={review} configData={configData} />
            ))}
          </CustomStackFullWidth>

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
            No reviews found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default ReviewList;

