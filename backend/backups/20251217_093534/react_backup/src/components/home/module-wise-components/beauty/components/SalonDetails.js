import React, { useState } from "react";
import {
  Box,
  Typography,
  Rating,
  Grid,
  Card,
  CardContent,
  Chip,
  Divider,
  Button,
  Pagination,
} from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import CustomContainer from "../../../../container";
import { useRouter } from "next/router";
import ServiceSuggestions from "./ServiceSuggestions";
import useGetSalonReviews from "../../../../../api-manage/hooks/react-query/beauty/useGetSalonReviews";
import useGetSalonDetails from "../../../../../api-manage/hooks/react-query/beauty/useGetSalonDetails";

const SalonDetails = ({ salonDetails, configData }) => {
  const router = useRouter();
  const [selectedServiceId, setSelectedServiceId] = useState(null);
  const [reviewsPage, setReviewsPage] = useState(1);
  const reviewsLimit = 5;
  const { data: fetchedSalon } = useGetSalonDetails(salonDetails?.id, !!salonDetails?.id);
  const { data: reviewsData } = useGetSalonReviews(
    salonDetails?.id,
    {
      limit: reviewsLimit,
      offset: (reviewsPage - 1) * reviewsLimit,
    },
    !!salonDetails?.id
  );

  const mergedSalon = fetchedSalon?.data || salonDetails;

  const handleBookNow = () => {
    router.push(`/beauty/booking/create?salon_id=${mergedSalon.id}`);
  };

  const handleBookConsultation = () => {
    router.push(`/beauty/consultations/book?salon_id=${mergedSalon.id}`);
  };

  const handleViewRetailProducts = () => {
    router.push(`/beauty/retail/products?salon_id=${mergedSalon.id}`);
  };

  const handleViewAllReviews = () => {
    router.push(`/beauty/salons/${mergedSalon.id}?tab=reviews`);
  };

  const allReviews = reviewsData?.data || mergedSalon?.reviews || [];
  const displayedReviews = allReviews;
  const totalPages = reviewsData?.last_page || Math.ceil((reviewsData?.total || displayedReviews.length || 1) / reviewsLimit);

  return (
    <CustomContainer>
      <CustomStackFullWidth spacing={4} sx={{ py: 4 }}>
        {/* Header Section */}
        <Box>
          <Typography variant="h4" fontWeight="bold" gutterBottom>
            {mergedSalon?.name || mergedSalon?.store?.name}
          </Typography>
          
          {mergedSalon?.avg_rating && (
            <Box display="flex" alignItems="center" gap={2} mb={2}>
              <Rating value={mergedSalon.avg_rating} readOnly />
              <Typography variant="body1">
                {mergedSalon.avg_rating} ({mergedSalon.total_reviews || 0} reviews)
              </Typography>
            </Box>
          )}

          {mergedSalon?.address && (
            <Typography variant="body1" color="text.secondary" gutterBottom>
              {mergedSalon.address}
            </Typography>
          )}

          {(mergedSalon?.phone || mergedSalon?.email || mergedSalon?.opening_time) && (
            <Box display="flex" flexWrap="wrap" gap={2} mt={2}>
              {mergedSalon?.phone && (
                <Chip label={`Phone: ${mergedSalon.phone}`} variant="outlined" />
              )}
              {mergedSalon?.email && (
                <Chip label={`Email: ${mergedSalon.email}`} variant="outlined" />
              )}
              {(mergedSalon?.opening_time || mergedSalon?.closing_time) && (
                <Chip
                  label={`Hours: ${mergedSalon.opening_time || "--"} - ${mergedSalon.closing_time || "--"}`}
                  variant="outlined"
                />
              )}
              {typeof mergedSalon?.is_open === "boolean" && (
                <Chip
                  label={mergedSalon.is_open ? "Open Now" : "Closed"}
                  color={mergedSalon.is_open ? "success" : "default"}
                  variant={mergedSalon.is_open ? "filled" : "outlined"}
                />
              )}
              {mergedSalon?.distance && (
                <Chip label={`Distance: ${mergedSalon.distance}`} variant="outlined" />
              )}
            </Box>
          )}

          {mergedSalon?.badges && mergedSalon.badges.length > 0 && (
            <Box display="flex" gap={1} mt={2} flexWrap="wrap">
              {mergedSalon.badges.map((badge, index) => (
                <Chip key={index} label={badge} color="primary" variant="outlined" />
              ))}
            </Box>
          )}

          <Box display="flex" gap={2} flexWrap="wrap" sx={{ mt: 3 }}>
            <Button
              variant="contained"
              color="primary"
              size="large"
              onClick={handleBookNow}
            >
              Book Now
            </Button>
            <Button
              variant="outlined"
              color="primary"
              size="large"
              onClick={handleBookConsultation}
            >
              Book Consultation
            </Button>
          </Box>
        </Box>

        <Divider />

        {/* Retail Products Section */}
        <Box>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
            <Typography variant="h5" fontWeight="bold">
              Retail Products
            </Typography>
            <Button variant="outlined" color="primary" onClick={handleViewRetailProducts}>
              View All Products
            </Button>
          </Box>
          <Typography variant="body2" color="text.secondary">
            Browse our retail products and add them to your cart.
          </Typography>
        </Box>

        <Divider />

        {/* Services Section */}
        {mergedSalon?.services && mergedSalon.services.length > 0 && (
          <Box>
            <Typography variant="h5" fontWeight="bold" gutterBottom>
              Services
            </Typography>
            <Grid container spacing={2}>
              {mergedSalon.services.map((service) => (
                <Grid item xs={12} sm={6} md={4} key={service.id}>
                  <Card
                    sx={{
                      cursor: "pointer",
                      border: selectedServiceId === service.id ? 2 : 0,
                      borderColor: "primary.main",
                    }}
                    onClick={() => setSelectedServiceId(service.id)}
                  >
                    <CardContent>
                      <Typography variant="h6">{service.name}</Typography>
                      <Typography variant="body2" color="text.secondary">
                        {service.description}
                      </Typography>
                      <Typography variant="h6" color="primary" mt={1}>
                        ${service.price}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Duration: {service.duration_minutes} minutes
                      </Typography>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
            {selectedServiceId && (
              <ServiceSuggestions
                serviceId={selectedServiceId}
                  salonId={mergedSalon.id}
                onAddToBooking={(suggestion) => {
                    router.push(`/beauty/booking/create?salon_id=${mergedSalon.id}&service_id=${suggestion.id}`);
                }}
              />
            )}
          </Box>
        )}

        {/* Staff Section */}
        {mergedSalon?.staff && mergedSalon.staff.length > 0 && (
          <Box>
            <Typography variant="h5" fontWeight="bold" gutterBottom>
              Our Staff
            </Typography>
            <Grid container spacing={2}>
              {mergedSalon.staff.map((staffMember) => (
                <Grid item xs={12} sm={6} md={4} key={staffMember.id}>
                  <Card>
                    <CardContent>
                      <Typography variant="h6">{staffMember.name}</Typography>
                      {staffMember.specializations && staffMember.specializations.length > 0 && (
                        <Box mt={1}>
                          {staffMember.specializations.map((spec, index) => (
                            <Chip key={index} label={spec} size="small" sx={{ mr: 0.5, mb: 0.5 }} />
                          ))}
                        </Box>
                      )}
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </Box>
        )}

        {/* Reviews Section */}
        {displayedReviews.length > 0 && (
          <Box>
            <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
              <Typography variant="h5" fontWeight="bold">
                Reviews ({reviewsData?.total ?? allReviews.length})
              </Typography>
              <Button variant="outlined" color="primary" onClick={handleViewAllReviews}>
                View All Reviews
              </Button>
            </Box>
            <CustomStackFullWidth spacing={2}>
              {displayedReviews.map((review) => (
                <Card key={review.id}>
                  <CardContent>
                    <Box display="flex" justifyContent="space-between" mb={1}>
                      <Typography variant="subtitle1" fontWeight="bold">
                        {review.user_name || "Anonymous"}
                      </Typography>
                      <Rating value={review.rating} readOnly size="small" />
                    </Box>
                    <Typography variant="body2" color="text.secondary">
                      {review.comment}
                    </Typography>
                    <Typography variant="caption" color="text.secondary" mt={1} display="block">
                      {review.created_at}
                    </Typography>
                  </CardContent>
                </Card>
              ))}
            </CustomStackFullWidth>

            {totalPages > 1 && (
              <Box display="flex" justifyContent="center" mt={2}>
                <Pagination
                  count={totalPages}
                  page={reviewsPage}
                  onChange={(event, value) => setReviewsPage(value)}
                  color="primary"
                />
              </Box>
            )}
          </Box>
        )}
      </CustomStackFullWidth>
    </CustomContainer>
  );
};

export default SalonDetails;

