/**
 * Safe Rendering Utilities
 * Prevents React Error #31: Objects are not valid as a React child
 */

/**
 * Safely convert any value to string for rendering
 * @param {any} value - Value to convert
 * @returns {string} Safe string representation
 */
export const safeString = (value) => {
  if (value === null || value === undefined) return '';
  if (typeof value === 'string') return value;
  if (typeof value === 'object') {
    return value?.message || JSON.stringify(value);
  }
  return String(value);
};

/**
 * Safely get error message for helperText
 * @param {any} error - Error value (string, object, or null)
 * @param {boolean} touched - Whether field was touched
 * @returns {string} Safe error message or empty string
 */
export const safeHelperText = (error, touched = true) => {
  return safeString(error);
};

/**
 * Safely render value in JSX
 * @param {any} value - Value to render
 * @returns {string|number|null} Safe value for rendering
 */
export const safeRender = (value) => {
  if (value === null || value === undefined) return null;
  if (typeof value === 'object') {
    return safeString(value);
  }
  return value;
};
