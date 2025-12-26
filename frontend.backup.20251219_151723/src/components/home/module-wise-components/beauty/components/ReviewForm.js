import React, { useState } from "react";
import {
  Typography,
  Box,
  TextField,
  Button,
  Rating,
  Card,
  CardContent,
  IconButton,
  CircularProgress,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useSubmitReview } from "../../../../../api-manage/hooks/react-query/beauty/useSubmitReview";
import toast from "react-hot-toast";
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";
import DeleteIcon from "@mui/icons-material/Delete";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";

const ReviewForm = ({ bookingId, onSuccess, onCancel }) => {
  const { mutate: submitReview, isLoading } = useSubmitReview();
  const [rating, setRating] = useState(0);
  const [comment, setComment] = useState("");
  const [attachments, setAttachments] = useState([]);
  const MAX_FILE_SIZE_MB = 5;

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    const validFiles = [];

    files.forEach((file) => {
      if (!file.type.startsWith("image/")) {
        toast.error("Please select only image files");
        return;
      }
      if (file.size > MAX_FILE_SIZE_MB * 1024 * 1024) {
        toast.error(`Each file must be under ${MAX_FILE_SIZE_MB} MB`);
        return;
      }
      validFiles.push(file);
    });

    if (validFiles.length > 0) {
      setAttachments((prev) => [...prev, ...validFiles]);
    }
    e.target.value = ""; // Reset input
  };

  const handleRemoveAttachment = (index) => {
    setAttachments((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!rating || rating < 1) {
      toast.error("Please provide a rating");
      return;
    }

    if (!comment.trim()) {
      toast.error("Please provide a comment");
      return;
    }

    submitReview(
      {
        booking_id: bookingId,
        rating,
        comment: comment.trim(),
        attachments: attachments.length > 0 ? attachments : undefined,
      },
      {
        onSuccess: (response) => {
          toast.success("Review submitted successfully");
          if (onSuccess) {
            onSuccess(response);
          }
        },
        onError: (error) => {
          toast.error(getBeautyErrorMessage(error) || "Failed to submit review");
        },
      }
    );
  };

  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <CustomStackFullWidth spacing={3}>
            <Typography variant="h6" fontWeight="bold">
              Submit Review
            </Typography>

            <Box>
              <Typography variant="subtitle1" gutterBottom>
                Rating *
              </Typography>
              <Rating
                value={rating}
                onChange={(event, newValue) => setRating(newValue)}
                size="large"
              />
            </Box>

            <TextField
              fullWidth
              label="Comment *"
              multiline
              rows={4}
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              required
            />

            <Box>
              <Typography variant="subtitle1" gutterBottom>
                Attachments (Optional)
              </Typography>
              <Button
                variant="outlined"
                component="label"
                startIcon={<CloudUploadIcon />}
                sx={{ mb: 2 }}
              >
                Upload Images
                <input
                  type="file"
                  hidden
                  multiple
                  accept="image/*"
                  onChange={handleFileChange}
                />
              </Button>

              {attachments.length > 0 && (
                <Box display="flex" flexWrap="wrap" gap={2} mt={2}>
                  {attachments.map((file, index) => (
                    <Box
                      key={index}
                      sx={{
                        position: "relative",
                        width: 100,
                        height: 100,
                        borderRadius: 1,
                        overflow: "hidden",
                      }}
                    >
                      <Box
                        component="img"
                        src={URL.createObjectURL(file)}
                        alt={`Preview ${index + 1}`}
                        sx={{
                          width: "100%",
                          height: "100%",
                          objectFit: "cover",
                        }}
                      />
                      <IconButton
                        size="small"
                        color="error"
                        sx={{
                          position: "absolute",
                          top: 0,
                          right: 0,
                          bgcolor: "rgba(0,0,0,0.5)",
                        }}
                        onClick={() => handleRemoveAttachment(index)}
                      >
                        <DeleteIcon fontSize="small" />
                      </IconButton>
                    </Box>
                  ))}
                </Box>
              )}
            </Box>

            <Box display="flex" gap={2}>
              {onCancel && (
                <Button
                  variant="outlined"
                  color="secondary"
                  onClick={onCancel}
                  fullWidth
                  disabled={isLoading}
                >
                  Cancel
                </Button>
              )}
              <Button
                type="submit"
                variant="contained"
                color="primary"
                fullWidth
                disabled={isLoading || !rating || !comment.trim()}
              >
                {isLoading ? (
                  <CircularProgress size={24} color="inherit" />
                ) : (
                  "Submit Review"
                )}
              </Button>
            </Box>
          </CustomStackFullWidth>
        </form>
      </CardContent>
    </Card>
  );
};

export default ReviewForm;

