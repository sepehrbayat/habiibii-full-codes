/**
 * Firebase Error Handler Utility
 * ابزار مدیریت خطای Firebase
 * 
 * Handles Firebase messaging errors gracefully
 * مدیریت خطاهای Firebase messaging به صورت graceful
 */

/**
 * Initialize Firebase messaging with error handling
 * راه‌اندازی Firebase messaging با مدیریت خطا
 * 
 * @param {Object} firebaseConfig Firebase configuration object
 * @param {string} topic Topic to subscribe to
 */
export function initializeFirebaseMessaging(firebaseConfig, topic) {
  // Check if Firebase is available
  // بررسی اینکه آیا Firebase در دسترس است
  if (typeof window === "undefined" || typeof firebase === "undefined") {
    console.warn("Firebase is not available in this environment");
    return;
  }

  try {
    // Initialize Firebase app
    // راه‌اندازی Firebase app
    let app;
    try {
      app = firebase.app();
    } catch (error) {
      // App doesn't exist, initialize it
      // App وجود ندارد، آن را راه‌اندازی کنید
      app = firebase.initializeApp(firebaseConfig);
    }

    // Get messaging instance
    // دریافت instance messaging
    const messaging = firebase.messaging();

    // Request permission and get token
    // درخواست مجوز و دریافت token
    requestNotificationPermission(messaging, topic);

    // Handle token refresh
    // مدیریت refresh token
    messaging.onTokenRefresh(() => {
      messaging.getToken()
        .then((token) => {
          if (token) {
            subscribeTokenToBackend(token, topic);
          }
        })
        .catch((error) => {
          console.warn("Error refreshing token:", error);
        });
    });

    // Handle background messages
    // مدیریت پیام‌های background
    messaging.onMessage((payload) => {
      console.log("Message received:", payload);
      // Handle foreground messages here
      // مدیریت پیام‌های foreground در اینجا
    });

  } catch (error) {
    console.error("Error initializing Firebase:", error);
  }
}

/**
 * Request notification permission
 * درخواست مجوز اعلان
 * 
 * @param {Object} messaging Firebase messaging instance
 * @param {string} topic Topic to subscribe to
 */
function requestNotificationPermission(messaging, topic) {
  if (!messaging) {
    console.warn("Firebase messaging is not available");
    return;
  }

  messaging
    .requestPermission()
    .then(() => {
      return messaging.getToken();
    })
    .then((token) => {
      if (token) {
        subscribeTokenToBackend(token, topic);
      } else {
        console.warn("No token available");
      }
    })
    .catch((error) => {
      // Handle permission denied or other errors gracefully
      // مدیریت رد مجوز یا سایر خطاها به صورت graceful
      if (error.code === "messaging/permission-blocked") {
        console.warn("Notification permission was blocked by user");
      } else if (error.code === "messaging/permission-default") {
        console.warn("Notification permission is in default state");
      } else {
        console.warn("Error getting permission or token:", error);
      }
    });
}

/**
 * Subscribe token to backend topic
 * اشتراک token در topic بک‌اند
 * 
 * @param {string} token FCM token
 * @param {string} topic Topic name
 */
function subscribeTokenToBackend(token, topic) {
  if (!token || !topic) {
    console.warn("Token or topic is missing");
    return;
  }

  const subscribeUrl = `${window.location.origin}/subscribeToTopic`;
  
  fetch(subscribeUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
    },
    body: JSON.stringify({ token: token, topic: topic }),
  })
    .then((response) => {
      if (response.status < 200 || response.status >= 400) {
        return response.text().then((text) => {
          throw new Error(`Error subscribing to topic: ${response.status} - ${text}`);
        });
      }
      console.log(`Successfully subscribed to "${topic}"`);
    })
    .catch((error) => {
      console.warn("Subscription error:", error);
    });
}

/**
 * Safe wrapper for Firebase messaging operations
 * Wrapper امن برای عملیات Firebase messaging
 * 
 * @param {Function} operation Function to execute
 * @param {string} fallbackMessage Fallback message if operation fails
 */
export function safeFirebaseOperation(operation, fallbackMessage = "Firebase operation failed") {
  try {
    if (typeof operation === "function") {
      return operation();
    }
  } catch (error) {
    console.warn(fallbackMessage, error);
    return null;
  }
}

