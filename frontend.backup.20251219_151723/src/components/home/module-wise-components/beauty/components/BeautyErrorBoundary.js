import React from "react";
import { Box, Typography, Button } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

class BeautyErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error("Beauty module error:", error, errorInfo);
  }

  handleRetry = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError) {
      return (
        <Box p={4} textAlign="center">
          <CustomStackFullWidth spacing={3}>
            <Typography variant="h5" color="error" gutterBottom>
              Something went wrong
            </Typography>
            <Typography variant="body1" color="text.secondary">
              {this.state.error?.message || "An unexpected error occurred in the Beauty module"}
            </Typography>
            <Button
              variant="contained"
              color="primary"
              onClick={this.handleRetry}
            >
              Try Again
            </Button>
          </CustomStackFullWidth>
        </Box>
      );
    }

    return this.props.children;
  }
}

export default BeautyErrorBoundary;

