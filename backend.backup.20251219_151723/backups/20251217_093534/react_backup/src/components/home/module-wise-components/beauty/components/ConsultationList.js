import React, { useEffect, useMemo, useState } from "react";
import { Typography, Box, Tabs, Tab, CircularProgress, FormControl, InputLabel, Select, MenuItem, Grid } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import ConsultationCard from "./ConsultationCard";
import useGetConsultations from "../../../../../api-manage/hooks/react-query/beauty/useGetConsultations";
import useGetSalons from "../../../../../api-manage/hooks/react-query/beauty/useGetSalons";
import { useRouter } from "next/router";

const ConsultationList = () => {
  const router = useRouter();
  const { salon_id: querySalonId } = router.query;
  const [consultationType, setConsultationType] = useState("");
  const [offset, setOffset] = useState(0);
  const [selectedSalonId, setSelectedSalonId] = useState(querySalonId || "");
  const limit = 25;

  const { data: salonsData, isLoading: salonsLoading } = useGetSalons(
    { limit: 50 },
    true
  );

  useEffect(() => {
    if (querySalonId) {
      setSelectedSalonId(querySalonId);
      return;
    }
    const firstSalonId = salonsData?.data?.[0]?.id;
    if (!selectedSalonId && firstSalonId) {
      setSelectedSalonId(firstSalonId);
    }
  }, [querySalonId, salonsData, selectedSalonId]);

  const hasSalonId = useMemo(() => Boolean(selectedSalonId), [selectedSalonId]);

  const { data, isLoading } = useGetConsultations(
    {
      limit,
      offset,
      salon_id: hasSalonId ? selectedSalonId : undefined,
      consultation_type: consultationType || undefined,
    },
    hasSalonId
  );

  const consultations = data?.data || [];

  const handleTypeChange = (event) => {
    setConsultationType(event.target.value);
    setOffset(0);
  };

  const handleSalonChange = (event) => {
    setSelectedSalonId(event.target.value);
    setOffset(0);
  };

  if (!salonsLoading && !salonsData?.data?.length) {
    return (
      <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
        <Typography variant="h4" fontWeight="bold">
          Consultations
        </Typography>
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No salons available to show consultations.
          </Typography>
        </Box>
      </CustomStackFullWidth>
    );
  }

  if (!hasSalonId) {
    return (
      <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
        <Typography variant="h4" fontWeight="bold">
          Consultations
        </Typography>
        <Box display="flex" justifyContent="space-between" alignItems="center" flexWrap="wrap" gap={2}>
          <FormControl sx={{ minWidth: 220 }}>
            <InputLabel>Select Salon</InputLabel>
            <Select
              value={selectedSalonId}
              label="Select Salon"
              onChange={handleSalonChange}
              disabled={salonsLoading}
            >
              {salonsData?.data?.map((salon) => (
                <MenuItem key={salon.id} value={salon.id}>
                  {salon.name || `Salon #${salon.id}`}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            Select a salon to view consultations.
          </Typography>
        </Box>
      </CustomStackFullWidth>
    );
  }

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Box display="flex" justifyContent="space-between" alignItems="center" flexWrap="wrap" gap={2}>
        <Typography variant="h4" fontWeight="bold">
          Consultations
        </Typography>
        <Box display="flex" gap={2} flexWrap="wrap">
          <FormControl sx={{ minWidth: 200 }}>
            <InputLabel>Select Salon</InputLabel>
            <Select
              value={selectedSalonId}
              label="Select Salon"
              onChange={handleSalonChange}
              disabled={salonsLoading}
            >
              {salonsData?.data?.map((salon) => (
                <MenuItem key={salon.id} value={salon.id}>
                  {salon.name || `Salon #${salon.id}`}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <FormControl sx={{ minWidth: 200 }}>
            <InputLabel>Filter by Type</InputLabel>
            <Select
              value={consultationType}
              label="Filter by Type"
              onChange={handleTypeChange}
            >
              <MenuItem value="">All Types</MenuItem>
              <MenuItem value="pre_consultation">Pre Consultation</MenuItem>
              <MenuItem value="post_consultation">Post Consultation</MenuItem>
            </Select>
          </FormControl>
        </Box>
      </Box>

      {isLoading ? (
        <Box display="flex" justifyContent="center" p={4}>
          <CircularProgress />
        </Box>
      ) : consultations.length > 0 ? (
        <Grid container spacing={3}>
          {consultations.map((consultation) => (
            <Grid item xs={12} sm={6} md={4} key={consultation.id}>
              <ConsultationCard consultation={consultation} />
            </Grid>
          ))}
        </Grid>
      ) : (
        <Box p={4} textAlign="center">
          <Typography variant="body1" color="text.secondary">
            No consultations found
          </Typography>
        </Box>
      )}
    </CustomStackFullWidth>
  );
};

export default ConsultationList;

