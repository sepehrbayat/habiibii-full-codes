import React, { useState } from "react";
import { FormControl, InputLabel, Select, MenuItem, Box, TextField, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import useGetCategories from "../../../../../api-manage/hooks/react-query/beauty/useGetCategories";

const SalonFilters = ({ onFilter }) => {
  const { data: categories } = useGetCategories();
  const [businessType, setBusinessType] = useState("");
  const [categoryId, setCategoryId] = useState("");
  const [minRating, setMinRating] = useState("");
  const [latitude, setLatitude] = useState("");
  const [longitude, setLongitude] = useState("");
  const [radius, setRadius] = useState("");

  const handleFilterChange = (filterType, value) => {
    if (filterType === "business_type") {
      setBusinessType(value);
      onFilter({ business_type: value || undefined });
    } else if (filterType === "category_id") {
      setCategoryId(value);
      onFilter({ category_id: value || undefined });
    } else if (filterType === "min_rating") {
      setMinRating(value);
      onFilter({ min_rating: value || undefined });
    } else if (filterType === "latitude") {
      setLatitude(value);
      onFilter({ latitude: value || undefined });
    } else if (filterType === "longitude") {
      setLongitude(value);
      onFilter({ longitude: value || undefined });
    } else if (filterType === "radius") {
      setRadius(value);
      onFilter({ radius: value || undefined });
    }
  };

  const handleCurrentLocation = () => {
    if (!navigator?.geolocation) {
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude.toFixed(6);
        const lng = pos.coords.longitude.toFixed(6);
        setLatitude(lat);
        setLongitude(lng);
        onFilter({ latitude: lat, longitude: lng, radius: radius || undefined });
      },
      () => {
        // silent failure
      }
    );
  };

  return (
    <CustomStackFullWidth>
      <Box display="flex" gap={2} flexWrap="wrap">
        <FormControl sx={{ minWidth: 150 }}>
          <InputLabel>Business Type</InputLabel>
          <Select
            value={businessType}
            label="Business Type"
            onChange={(e) => handleFilterChange("business_type", e.target.value)}
          >
            <MenuItem value="">All</MenuItem>
            <MenuItem value="salon">Salon</MenuItem>
            <MenuItem value="clinic">Clinic</MenuItem>
          </Select>
        </FormControl>

        {categories?.data && (
          <FormControl sx={{ minWidth: 150 }}>
            <InputLabel>Category</InputLabel>
            <Select
              value={categoryId}
              label="Category"
              onChange={(e) => handleFilterChange("category_id", e.target.value)}
            >
              <MenuItem value="">All Categories</MenuItem>
              {categories.data.map((category) => (
                <MenuItem key={category.id} value={category.id}>
                  {category.name}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        )}

        <FormControl sx={{ minWidth: 150 }}>
          <InputLabel>Min Rating</InputLabel>
          <Select
            value={minRating}
            label="Min Rating"
            onChange={(e) => handleFilterChange("min_rating", e.target.value)}
          >
            <MenuItem value="">All</MenuItem>
            <MenuItem value="4">4+ Stars</MenuItem>
            <MenuItem value="4.5">4.5+ Stars</MenuItem>
            <MenuItem value="5">5 Stars</MenuItem>
          </Select>
        </FormControl>

        <TextField
          label="Latitude"
          value={latitude}
          onChange={(e) => handleFilterChange("latitude", e.target.value)}
          sx={{ minWidth: 150 }}
          placeholder="e.g. 40.7128"
        />
        <TextField
          label="Longitude"
          value={longitude}
          onChange={(e) => handleFilterChange("longitude", e.target.value)}
          sx={{ minWidth: 150 }}
          placeholder="e.g. -74.0060"
        />
        <TextField
          label="Radius (km)"
          type="number"
          value={radius}
          onChange={(e) => handleFilterChange("radius", e.target.value)}
          sx={{ minWidth: 140 }}
          inputProps={{ min: 1, max: 50, step: 1 }}
        />
        <Button variant="outlined" onClick={handleCurrentLocation}>
          Use Current Location
        </Button>
      </Box>
    </CustomStackFullWidth>
  );
};

export default SalonFilters;

