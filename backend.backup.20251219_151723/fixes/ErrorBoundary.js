import React from "react";
import { Box, Typography, Button, Stack } from "@mui/material";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";

/**
 * Global Error Boundary Component
 * کامپوننت Error Boundary سراسری
 * 
 * Catches JavaScript errors anywhere in the child component tree,
 * logs those errors, and displays a fallback UI instead of crashing
 * گرفتن خطاهای JavaScript در هر جای درخت کامپوننت فرزند،
 * ثبت آن خطاها و نمایش UI جایگزین به جای crash
 */
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { 
      hasError: false, 
      error: null,
      errorInfo: null
    };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI
    // به‌روزرسانی state تا render بعدی UI جایگزین را نشان دهد
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    // Log error to console for debugging
    // ثبت خطا در console برای debugging
    console.error("ErrorBoundary caught an error:", error, errorInfo);
    
    // You can also log the error to an error reporting service here
    // می‌توانید خطا را به یک سرویس گزارش خطا نیز ارسال کنید
    
    this.setState({
      errorInfo: errorInfo
    });
  }

  handleRetry = () => {
    // Reset error state to allow retry
    // بازنشانی state خطا برای اجازه تلاش مجدد
    this.setState({ 
      hasError: false, 
      error: null,
      errorInfo: null
    });
  };

  handleReload = () => {
    // Reload the page as last resort
    // بارگذاری مجدد صفحه به عنوان آخرین راه حل
    window.location.reload();
  };

  render() {
    if (this.state.hasError) {
      // Render custom fallback UI
      // رندر UI جایگزین سفارشی
      return (
        <Box 
          p={4} 
          textAlign="center"
          sx={{
            minHeight: "400px",
            display: "flex",
            alignItems: "center",
            justifyContent: "center"
          }}
        >
          <CustomStackFullWidth spacing={3} maxWidth="600px">
            <Typography variant="h5" color="error" gutterBottom>
              Something went wrong
            </Typography>
            <Typography variant="body1" color="text.secondary">
              {this.state.error?.message || "An unexpected error occurred. Please try again."}
            </Typography>
            {process.env.NODE_ENV === "development" && this.state.errorInfo && (
              <Box 
                sx={{ 
                  mt: 2, 
                  p: 2, 
                  bgcolor: "grey.100", 
                  borderRadius: 1,
                  textAlign: "left",
                  maxHeight: "200px",
                  overflow: "auto"
                }}
              >
                <Typography variant="caption" component="pre" sx={{ fontSize: "0.75rem" }}>
                  {this.state.errorInfo.componentStack}
                </Typography>
              </Box>
            )}
            <Stack direction="row" spacing={2} justifyContent="center">
              <Button
                variant="contained"
                color="primary"
                onClick={this.handleRetry}
              >
                Try Again
              </Button>
              <Button
                variant="outlined"
                color="secondary"
                onClick={this.handleReload}
              >
                Reload Page
              </Button>
            </Stack>
          </CustomStackFullWidth>
        </Box>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;

