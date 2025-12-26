import React, { useState } from "react";
import { Box, Stepper, Step, StepLabel, Button, Typography } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import SalonRegistrationForm from "./SalonRegistrationForm";
import DocumentsUpload from "./DocumentsUpload";
import WorkingHoursForm from "./WorkingHoursForm";

const steps = ["Business Information", "Working Hours", "Documents"];

const RegistrationWizard = () => {
  const [activeStep, setActiveStep] = useState(0);

  const handleNext = () => {
    setActiveStep((prev) => prev + 1);
  };

  const handleBack = () => {
    setActiveStep((prev) => prev - 1);
  };

  const renderStepContent = (step) => {
    switch (step) {
      case 0:
        return <SalonRegistrationForm onSuccess={handleNext} />;
      case 1:
        return <WorkingHoursForm />;
      case 2:
        return <DocumentsUpload />;
      default:
        return null;
    }
  };

  return (
    <CustomStackFullWidth spacing={3} sx={{ py: 4 }}>
      <Typography variant="h4" fontWeight="bold">
        Salon Registration
      </Typography>

      <Stepper activeStep={activeStep}>
        {steps.map((label) => (
          <Step key={label}>
            <StepLabel>{label}</StepLabel>
          </Step>
        ))}
      </Stepper>

      <Box sx={{ mt: 4 }}>
        {renderStepContent(activeStep)}
      </Box>

      <Box display="flex" justifyContent="space-between" sx={{ mt: 4 }}>
        <Button
          disabled={activeStep === 0}
          onClick={handleBack}
        >
          Back
        </Button>
        {activeStep < steps.length - 1 && (
          <Button variant="contained" onClick={handleNext}>
            Next
          </Button>
        )}
      </Box>
    </CustomStackFullWidth>
  );
};

export default RegistrationWizard;

