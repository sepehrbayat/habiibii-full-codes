import React from "react";
import { Card, CardContent, Typography, Box, Rating, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { getImageUrl } from "utils/CustomFunctions";
import { useSelector } from "react-redux";

const ReviewCard = ({ review, configData }) => {
  const getStatusColor = (status) => {
    switch (status) {
      case "approved":
        return "success";
      case "pending":
        return "warning";
      case "rejected":
        return "error";
      default:
        return "default";
    }
  };

  return (
    <Card>
      <CardContent>
        <CustomStackFullWidth spacing={2}>
          <Box display="flex" justifyContent="space-between" alignItems="flex-start">
            <Box>
              <Typography variant="h6" fontWeight="bold">
                {review.salon_name || review.service_name || "Review"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {review.created_at || "Date not available"}
              </Typography>
            </Box>
            {review.status && (
              <Chip
                label={review.status}
                color={getStatusColor(review.status)}
                size="small"
              />
            )}
          </Box>

          <Box display="flex" alignItems="center" gap={1}>
            <Rating value={review.rating || 0} readOnly size="small" />
            <Typography variant="body2" color="text.secondary">
              {review.rating}/5
            </Typography>
          </Box>

          {review.comment && (
            <Typography variant="body1">{review.comment}</Typography>
          )}

          {(review.user || review.service || review.staff) && (
            <Box display="flex" flexWrap="wrap" gap={1}>
              {review.user && (
                <Chip
                  label={`User: ${review.user.name || review.user_name || "N/A"}`}
                  size="small"
                  variant="outlined"
                />
              )}
              {review.service && (
                <Chip
                  label={`Service: ${review.service.name || review.service_name}`}
                  size="small"
                  variant="outlined"
                />
              )}
              {review.staff && (
                <Chip
                  label={`Staff: ${review.staff.name || review.staff_id}`}
                  size="small"
                  variant="outlined"
                />
              )}
            </Box>
          )}

          {review.attachments && review.attachments.length > 0 && (
            <Box display="flex" flexWrap="wrap" gap={1} mt={1}>
              {review.attachments.map((attachment, index) => (
                <Box
                  key={index}
                  component="img"
                  src={getImageUrl({ value: attachment }, "review_attachment_url", configData)}
                  alt={`Review attachment ${index + 1}`}
                  sx={{
                    width: 100,
                    height: 100,
                    objectFit: "cover",
                    borderRadius: 1,
                    cursor: "pointer",
                  }}
                  onClick={() => {
                    window.open(
                      getImageUrl({ value: attachment }, "review_attachment_url", configData),
                      "_blank"
                    );
                  }}
                />
              ))}
            </Box>
          )}

          {review.booking_reference && (
            <Typography variant="body2" color="text.secondary">
              Booking: {review.booking_reference}
            </Typography>
          )}
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default ReviewCard;

