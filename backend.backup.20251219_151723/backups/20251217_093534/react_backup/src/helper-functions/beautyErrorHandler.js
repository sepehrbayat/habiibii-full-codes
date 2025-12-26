/**
 * Helper function to extract error messages from Laravel API responses
 * Laravel returns errors in format: { errors: [{ code: '...', message: '...' }] }
 * or sometimes: { message: '...' }
 */
export const getBeautyErrorMessage = (error) => {
  // Check for Laravel validation errors array
  if (error?.response?.data?.errors?.length > 0) {
    const firstError = error.response.data.errors[0];
    // Return first error message, or code if message doesn't exist
    return firstError.message || firstError.code || firstError;
  }
  
  // Check for single message
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }
  
  // Check for error message in different formats
  if (error?.response?.data?.error) {
    return error.response.data.error;
  }
  
  // Fallback to generic message
  return error?.message || "An error occurred";
};

/**
 * Get all error messages from Laravel API response
 * Returns array of error messages
 */
export const getBeautyErrorMessages = (error) => {
  if (error?.response?.data?.errors?.length > 0) {
    return error.response.data.errors.map(err => err.message || err);
  }
  
  if (error?.response?.data?.message) {
    return [error.response.data.message];
  }
  
  return [error?.message || "An error occurred"];
};

/**
 * Normalize Laravel beauty errors into array of { code, message }
 */
export const handleBeautyError = (error) => {
  if (error?.response?.data?.errors) {
    const errors = error.response.data.errors;
    if (Array.isArray(errors) && errors.length > 0) {
      return errors.map((err) => ({
        code: err.code || "error",
        message: err.message || "An error occurred",
      }));
    }
  }

  if (error?.response?.data?.message) {
    return [
      {
        code: "error",
        message: error.response.data.message,
      },
    ];
  }

  return [
    {
      code: "error",
      message: error?.message || "An error occurred",
    },
  ];
};

