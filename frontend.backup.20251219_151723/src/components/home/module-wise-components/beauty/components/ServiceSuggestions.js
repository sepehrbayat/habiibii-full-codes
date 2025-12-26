import React from "react";
import { Typography, Box, Card, CardContent, Button, Grid, CircularProgress } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetServiceSuggestions from "../../../../../api-manage/hooks/react-query/beauty/useGetServiceSuggestions";

const ServiceSuggestions = ({ serviceId, salonId, onAddToBooking }) => {
  const { data, isLoading } = useGetServiceSuggestions(serviceId, salonId, !!serviceId);

  const suggestions = data?.data || [];

  if (!serviceId) {
    return null;
  }

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" p={2}>
        <CircularProgress size={24} />
      </Box>
    );
  }

  if (suggestions.length === 0) {
    return null;
  }

  return (
    <CustomStackFullWidth spacing={2} sx={{ mt: 3 }}>
      <Typography variant="h6" fontWeight="bold">
        Suggested Services
      </Typography>
      <Grid container spacing={2}>
        {suggestions.map((suggestion) => (
          <Grid item xs={12} sm={6} md={4} key={suggestion.id}>
            <Card>
              <CardContent>
                <CustomStackFullWidth spacing={2}>
                  <Box>
                    <Typography variant="h6" fontWeight="bold">
                      {suggestion.name}
                    </Typography>
                    {suggestion.description && (
                      <Typography variant="body2" color="text.secondary">
                        {suggestion.description}
                      </Typography>
                    )}
                  </Box>

                  <Box>
                    <Typography variant="h6" color="primary" fontWeight="bold">
                      ${suggestion.price || 0}
                    </Typography>
                    {suggestion.duration_minutes && (
                      <Typography variant="body2" color="text.secondary">
                        Duration: {suggestion.duration_minutes} minutes
                      </Typography>
                    )}
                  </Box>

                  {onAddToBooking && (
                    <Button
                      variant="outlined"
                      color="primary"
                      size="small"
                      onClick={() => onAddToBooking(suggestion)}
                      fullWidth
                    >
                      Add to Booking
                    </Button>
                  )}
                </CustomStackFullWidth>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </CustomStackFullWidth>
  );
};

export default ServiceSuggestions;

