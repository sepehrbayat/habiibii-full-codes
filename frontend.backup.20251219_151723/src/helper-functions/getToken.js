import { getVendorToken as getVendorTokenFromContext } from "../contexts/vendor-auth-context";

/**
 * Get authentication token from localStorage
 * دریافت توکن احراز هویت از localStorage
 * 
 * @returns {string|null} Token string or null if not found
 */
export const getToken = () => {
  // Check if we're in browser environment
  // بررسی اینکه آیا در محیط مرورگر هستیم
  if (typeof window === "undefined") {
    return null;
  }

  try {
    const token = window.localStorage.getItem("token");
    // Return null if token is empty string or null
    // برگرداندن null اگر token خالی یا null باشد
    return token && token.trim() !== "" ? token : null;
  } catch (error) {
    // Handle localStorage access errors (e.g., in private browsing)
    // مدیریت خطاهای دسترسی به localStorage (مثلاً در حالت private browsing)
    console.warn("Error accessing localStorage:", error);
    return null;
  }
};

// Re-export the context-aware implementation to keep a single source of truth
export const getVendorToken = getVendorTokenFromContext;

/**
 * Get guest ID from localStorage
 * دریافت شناسه مهمان از localStorage
 * 
 * @returns {string|null} Guest ID string or null if not found
 */
export const getGuestId = () => {
  if (typeof window === "undefined") {
    return null;
  }

  try {
    const guestId = window.localStorage.getItem("guest_id");
    return guestId && guestId.trim() !== "" ? guestId : null;
  } catch (error) {
    console.warn("Error accessing localStorage for guest_id:", error);
    return null;
  }
};

