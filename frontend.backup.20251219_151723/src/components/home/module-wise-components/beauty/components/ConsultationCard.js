import React from "react";
import { Card, CardContent, Typography, Box, Button, Chip } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { useRouter } from "next/router";

const ConsultationCard = ({ consultation, onBook }) => {
  const router = useRouter();

  const handleBook = () => {
    if (onBook) {
      onBook(consultation);
    } else {
      router.push(`/beauty/consultations/book?consultation_id=${consultation.id}&salon_id=${consultation.salon_id || router.query.salon_id}`);
    }
  };

  const getTypeColor = (type) => {
    switch (type) {
      case "pre_consultation":
        return "primary";
      case "post_consultation":
        return "secondary";
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
                {consultation.name || consultation.consultation_type || "Consultation"}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                {consultation.description || ""}
              </Typography>
            </Box>
            {consultation.consultation_type && (
              <Chip
                label={consultation.consultation_type.replace("_", " ")}
                color={getTypeColor(consultation.consultation_type)}
                size="small"
              />
            )}
          </Box>

          <Box display="flex" gap={2} flexWrap="wrap">
            {consultation.price && (
              <Typography variant="body1" fontWeight="bold">
                Price: ${consultation.price}
              </Typography>
            )}
            {consultation.duration_minutes && (
              <Typography variant="body2" color="text.secondary">
                Duration: {consultation.duration_minutes} minutes
              </Typography>
            )}
          </Box>

          <Button
            variant="contained"
            color="primary"
            onClick={handleBook}
            fullWidth
          >
            Book Consultation
          </Button>
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default ConsultationCard;

