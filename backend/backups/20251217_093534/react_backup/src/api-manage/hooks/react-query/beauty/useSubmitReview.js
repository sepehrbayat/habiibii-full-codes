import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import MainApi from "../../../MainApi";

const submitReview = async (reviewData) => {
  const hasAttachments =
    Array.isArray(reviewData.attachments) && reviewData.attachments.length > 0;

  // If attachments are provided, use FormData
  if (hasAttachments) {
    const formData = new FormData();
    formData.append("booking_id", reviewData.booking_id);
    formData.append("rating", reviewData.rating);
    if (reviewData.comment) {
      formData.append("comment", reviewData.comment);
    }

    reviewData.attachments.forEach((file, index) => {
      formData.append(`attachments[${index}]`, file);
    });

    // Use MainApi directly to ensure proper FormData handling
    const { data } = await MainApi.post("/api/v1/beautybooking/reviews", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return data;
  } else if (reviewData.attachments instanceof File || reviewData.attachments instanceof Blob) {
    const formData = new FormData();
    formData.append("booking_id", reviewData.booking_id);
    formData.append("rating", reviewData.rating);
    if (reviewData.comment) {
      formData.append("comment", reviewData.comment);
    }
    formData.append("attachments[0]", reviewData.attachments);

    const { data } = await MainApi.post("/api/v1/beautybooking/reviews", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return data;
  }

  // If no attachments, send as regular JSON
  const { data } = await BeautyApi.submitReview(reviewData);
  return data;
};

export const useSubmitReview = () => {
  return useMutation("beauty-submit-review", submitReview);
};

