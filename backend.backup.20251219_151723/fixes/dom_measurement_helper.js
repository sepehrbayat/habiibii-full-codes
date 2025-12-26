/**
 * DOM Measurement Helper Utilities
 * ابزارهای کمکی اندازه‌گیری DOM
 * 
 * Safe DOM element measurement functions with null checks
 * توابع اندازه‌گیری امن المان DOM با بررسی null
 */

/**
 * Safely get element offset height
 * دریافت امن ارتفاع offset المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Offset height or 0 if element is null
 */
export function getOffsetHeight(element) {
  if (!element || typeof element.offsetHeight === "undefined") {
    return 0;
  }
  return element.offsetHeight || 0;
}

/**
 * Safely get element offset width
 * دریافت امن عرض offset المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Offset width or 0 if element is null
 */
export function getOffsetWidth(element) {
  if (!element || typeof element.offsetWidth === "undefined") {
    return 0;
  }
  return element.offsetWidth || 0;
}

/**
 * Safely get element client height
 * دریافت امن ارتفاع client المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Client height or 0 if element is null
 */
export function getClientHeight(element) {
  if (!element || typeof element.clientHeight === "undefined") {
    return 0;
  }
  return element.clientHeight || 0;
}

/**
 * Safely get element client width
 * دریافت امن عرض client المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Client width or 0 if element is null
 */
export function getClientWidth(element) {
  if (!element || typeof element.clientWidth === "undefined") {
    return 0;
  }
  return element.clientWidth || 0;
}

/**
 * Safely get element bounding client rect
 * دریافت امن bounding client rect المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {DOMRect|null} Bounding rect or null if element is null
 */
export function getBoundingClientRect(element) {
  if (!element || typeof element.getBoundingClientRect !== "function") {
    return null;
  }
  
  try {
    return element.getBoundingClientRect();
  } catch (error) {
    console.warn("Error getting bounding client rect:", error);
    return null;
  }
}

/**
 * Safely get element scroll height
 * دریافت امن ارتفاع scroll المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Scroll height or 0 if element is null
 */
export function getScrollHeight(element) {
  if (!element || typeof element.scrollHeight === "undefined") {
    return 0;
  }
  return element.scrollHeight || 0;
}

/**
 * Safely get element scroll width
 * دریافت امن عرض scroll المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {number} Scroll width or 0 if element is null
 */
export function getScrollWidth(element) {
  if (!element || typeof element.scrollWidth === "undefined") {
    return 0;
  }
  return element.scrollWidth || 0;
}

/**
 * Check if element exists and is visible
 * بررسی وجود و قابل مشاهده بودن المان
 * 
 * @param {HTMLElement|null} element DOM element
 * @returns {boolean} True if element exists and is visible
 */
export function isElementVisible(element) {
  if (!element) {
    return false;
  }
  
  try {
    const rect = getBoundingClientRect(element);
    if (!rect) {
      return false;
    }
    
    return (
      rect.width > 0 &&
      rect.height > 0 &&
      element.offsetParent !== null
    );
  } catch (error) {
    console.warn("Error checking element visibility:", error);
    return false;
  }
}

/**
 * Wait for element to be available in DOM
 * انتظار برای در دسترس بودن المان در DOM
 * 
 * @param {string} selector CSS selector
 * @param {number} timeout Timeout in milliseconds
 * @returns {Promise<HTMLElement|null>} Element or null if timeout
 */
export function waitForElement(selector, timeout = 5000) {
  return new Promise((resolve) => {
    if (typeof window === "undefined" || typeof document === "undefined") {
      resolve(null);
      return;
    }

    const element = document.querySelector(selector);
    if (element) {
      resolve(element);
      return;
    }

    const observer = new MutationObserver((mutations, obs) => {
      const element = document.querySelector(selector);
      if (element) {
        obs.disconnect();
        resolve(element);
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    setTimeout(() => {
      observer.disconnect();
      resolve(null);
    }, timeout);
  });
}

