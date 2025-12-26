import React from "react";
import { Card, CardContent, CardMedia, Typography, Box, Rating, Chip } from "@mui/material";
import { useRouter } from "next/router";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

const SalonCard = ({ salon }) => {
  const router = useRouter();

  const handleClick = () => {
    router.push(`/beauty/salons/${salon.id}`);
  };

  return (
    <Card
      sx={{
        cursor: "pointer",
        height: "100%",
        display: "flex",
        flexDirection: "column",
        "&:hover": {
          boxShadow: 6,
        },
      }}
      onClick={handleClick}
    >
      {salon.image && (
        <CardMedia
          component="img"
          height="200"
          image={salon.image}
          alt={salon.name}
        />
      )}
      <CardContent sx={{ flexGrow: 1 }}>
        <CustomStackFullWidth spacing={1}>
          <Typography variant="h6" fontWeight="bold">
            {salon.name || salon.store?.name}
          </Typography>
          
          {salon.business_type && (
            <Chip
              label={salon.business_type}
              size="small"
              color="primary"
              variant="outlined"
            />
          )}

          {salon.avg_rating && (
            <Box display="flex" alignItems="center" gap={1}>
              <Rating value={salon.avg_rating} readOnly size="small" />
              <Typography variant="body2" color="text.secondary">
                ({salon.total_reviews || 0})
              </Typography>
            </Box>
          )}

          {salon.address && (
            <Typography variant="body2" color="text.secondary" noWrap>
              {salon.address}
            </Typography>
          )}

          {salon.badges && salon.badges.length > 0 && (
            <Box display="flex" gap={0.5} flexWrap="wrap">
              {salon.badges.slice(0, 3).map((badge, index) => (
                <Chip
                  key={index}
                  label={badge}
                  size="small"
                  variant="outlined"
                />
              ))}
            </Box>
          )}
        </CustomStackFullWidth>
      </CardContent>
    </Card>
  );
};

export default SalonCard;

